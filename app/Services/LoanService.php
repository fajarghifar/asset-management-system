<?php

namespace App\Services;

use Exception;
use App\Models\Loan;
use App\Models\Asset;
use App\Enums\LoanStatus;
use App\Enums\AssetStatus;
use App\Enums\LoanItemType;
use App\Models\ConsumableStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class LoanService
{
    public function __construct(
        protected AssetService $assetService,
        protected ConsumableStockService $stockService
    ) {}

    /**
     * Create a new Loan with items.
     *
     * @param array $data Loan data (user_id, loan_date, etc.)
     * @param array $items Array of items (type, asset_id/stock_id, qty)
     * @return Loan
     * @throws Exception
     */
    public function createLoan(array $data, array $items): Loan
    {
        return DB::transaction(function () use ($data, $items) {
            try {
                // Pre-validate availability
                foreach ($items as $item) {
                    if ($item['type'] === LoanItemType::Asset->value) {
                        $this->validateAssetAvailability($item['asset_id']);
                    } elseif ($item['type'] === LoanItemType::Consumable->value) {
                        $this->validateStockAvailability($item['consumable_stock_id'], $item['quantity_borrowed']);
                    }
                }

                $loan = Loan::create([
                    'user_id' => $data['user_id'] ?? null,
                    'borrower_name' => $data['borrower_name'] ?? null,
                    'code' => $data['code'],
                    'purpose' => $data['purpose'] ?? null,
                    'loan_date' => $data['loan_date'],
                    'due_date' => $data['due_date'],
                    'status' => LoanStatus::Pending,
                    'notes' => $data['notes'] ?? null,
                    'proof_image' => $data['proof_image'] ?? null,
                ]);

                foreach ($items as $item) {
                    $loan->items()->create([
                        'type' => $item['type'],
                        'asset_id' => $item['asset_id'] ?? null,
                        'consumable_stock_id' => $item['consumable_stock_id'] ?? null,
                        'quantity_borrowed' => $item['quantity_borrowed'],
                        'quantity_returned' => 0,
                    ]);
                }

                return $loan;

            } catch (Exception $e) {
                Log::error("Failed to create loan: " . $e->getMessage());
                throw $e;
            }
        });
    }

    public function updateLoan(Loan $loan, array $data, array $items): Loan
    {
        return DB::transaction(function () use ($loan, $data, $items) {
            if ($loan->status !== LoanStatus::Pending) {
                throw new Exception("Cannot edit loan that is not in Pending status.");
            }

            // Re-validate availability
            foreach ($items as $item) {
                if ($item['type'] === LoanItemType::Asset->value) {
                    $this->validateAssetAvailability($item['asset_id']);
                } elseif ($item['type'] === LoanItemType::Consumable->value) {
                    $this->validateStockAvailability($item['consumable_stock_id'], $item['quantity_borrowed']);
                }
            }

            $loan->update([
                'user_id' => Auth::id(),
                'borrower_name' => $data['borrower_name'] ?? null,
                'purpose' => $data['purpose'] ?? null,
                'loan_date' => $data['loan_date'],
                'due_date' => $data['due_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            if (isset($data['proof_image'])) {
                $loan->update(['proof_image' => $data['proof_image']]);
            }

            // Sync items by replacing them
            $loan->items()->delete();

            foreach ($items as $item) {
                $loan->items()->create([
                    'type' => $item['type'],
                    'asset_id' => $item['asset_id'] ?? null,
                    'consumable_stock_id' => $item['consumable_stock_id'] ?? null,
                    'quantity_borrowed' => $item['quantity_borrowed'],
                    'quantity_returned' => 0,
                ]);
            }

            return $loan;
        });
    }

    public function approveLoan(Loan $loan): void
    {
        DB::transaction(function () use ($loan) {
            if ($loan->status !== LoanStatus::Pending) {
                throw new Exception("Loan status must be pending to approve. Current status: {$loan->status->value}");
            }

            foreach ($loan->items as $item) {
                if ($item->type === LoanItemType::Asset) {
                    $asset = Asset::find($item->asset_id);
                    if ($asset) {
                        if ($asset->status !== AssetStatus::InStock) {
                            throw new Exception("Asset {$asset->asset_tag} is no longer available.");
                        }
                        $this->assetService->updateStatus($asset, AssetStatus::Loaned, "Loan Approved: {$loan->code}");
                    }
                } elseif ($item->type === LoanItemType::Consumable) {
                    $stock = ConsumableStock::find($item->consumable_stock_id);
                    if ($stock) {
                        if ($stock->quantity < $item->quantity_borrowed) {
                            throw new Exception("Insufficient stock for item {$stock->product?->name}.");
                        }
                        $this->stockService->updateStock($stock, [
                            'quantity' => $stock->quantity - $item->quantity_borrowed
                        ]);
                    }
                }
            }

            $loan->update(['status' => LoanStatus::Approved]);
        });
    }

    public function rejectLoan(Loan $loan, ?string $reason = null): void
    {
        if ($loan->status !== LoanStatus::Pending) {
            throw new Exception("Only pending loans can be rejected.");
        }
        $loan->update([
            'status' => LoanStatus::Rejected,
            'notes' => $reason ? $loan->notes . "\nRejection Reason: " . $reason : $loan->notes
        ]);
    }

    public function restoreLoan(Loan $loan): void
    {
        if ($loan->status !== LoanStatus::Rejected) {
            throw new Exception("Only rejected loans can be restored.");
        }

        $loan->update([
            'status' => LoanStatus::Pending,
            'notes' => $loan->notes . "\n[System] Restored to Pending at " . now()->toDateTimeString(),
        ]);
    }

    public function returnItems(Loan $loan, array $returnDetails): void
    {
        DB::transaction(function () use ($loan, $returnDetails) {
            foreach ($returnDetails as $itemId => $returnData) {
                $item = $loan->items()->find($itemId);

                if (!$item || $item->loan_id !== $loan->id) {
                    continue;
                }

                if ($item->type === LoanItemType::Asset) {
                    if (!empty($returnData['is_returned'])) {
                        $asset = Asset::find($item->asset_id);
                        if ($asset && $asset->status === AssetStatus::Loaned) {
                            $this->assetService->updateStatus($asset, AssetStatus::InStock, "Returned from Loan: {$loan->code}");
                        }
                        $item->update([
                            'quantity_returned' => 1,
                            'returned_at' => now(),
                        ]);
                    }
                } elseif ($item->type === LoanItemType::Consumable) {
                    $qtyReturning = (int) ($returnData['quantity_returned'] ?? 0);

                    if ($qtyReturning < 0) {
                        continue;
                    }

                    $remainingToReturn = $item->quantity_borrowed - $item->quantity_returned;
                    if ($qtyReturning > $remainingToReturn) {
                        throw new Exception("Cannot return more than borrowed/remaining quantity for item ID {$itemId}.");
                    }

                    if ($qtyReturning > 0) {
                        $stock = ConsumableStock::find($item->consumable_stock_id);
                        if ($stock) {
                            $this->stockService->updateStock($stock, [
                                'quantity' => $stock->quantity + $qtyReturning
                            ]);
                        }
                    }

                    $item->increment('quantity_returned', $qtyReturning);
                    $item->update(['returned_at' => now()]);
                }
            }

            $loan->refresh();

            $allSettled = $loan->items->every(fn($i) => !is_null($i->returned_at));

            if ($allSettled) {
                $loan->update([
                    'status' => LoanStatus::Closed,
                    'returned_date' => now()
                ]);
            }
        });
    }

    protected function validateAssetAvailability(?int $assetId): void
    {
        if (!$assetId) {
            return;
        }

        $asset = Asset::find($assetId);
        if (!$asset) {
            throw new Exception("Asset not found with ID: {$assetId}");
        }
        if ($asset->status !== AssetStatus::InStock) {
            throw new Exception("Asset {$asset->asset_tag} is currently not available (Status: {$asset->status->getLabel()}).");
        }
    }

    protected function validateStockAvailability(?int $stockId, int $qty): void
    {
        if (!$stockId) {
            return;
        }

        $stock = ConsumableStock::find($stockId);
        if (!$stock) {
            throw new Exception("Stock item not found.");
        }
        if ($stock->quantity < $qty) {
            throw new Exception("Insufficient stock for {$stock->product?->name}. Requested: {$qty}, Available: {$stock->quantity}");
        }
    }

    public function deleteLoan(Loan $loan): void
    {
        if ($loan->status !== LoanStatus::Pending && $loan->status !== LoanStatus::Rejected) {
            throw new Exception("Only Pending or Rejected loans can be deleted.");
        }

        DB::transaction(function () use ($loan) {
            $loan->items()->delete();
            $loan->delete();
        });
    }

    public function generateTransactionCode(): string
    {
        $dateCode = now()->format('ymd');
        $prefix = "L.{$dateCode}.";

        $lastLoan = Loan::where('code', 'like', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();

        if ($lastLoan) {
            $lastSequence = (int) substr($lastLoan->code, strlen($prefix));
            $newSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '001';
        }

        return $prefix . $newSequence;
    }
}
