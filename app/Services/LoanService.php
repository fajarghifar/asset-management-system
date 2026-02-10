<?php

namespace App\Services;


use Throwable;
use App\Models\Loan;
use App\Models\Asset;
use App\DTOs\LoanData;
use App\Enums\LoanStatus;
use App\Enums\AssetStatus;
use App\Enums\LoanItemType;
use App\Models\ConsumableStock;
use App\DTOs\ConsumableStockData;
use App\Exceptions\LoanException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LoanService
{
    public function __construct(
        protected AssetService $assetService,
        protected ConsumableStockService $stockService
    ) {}

    /**
     * Create a new Loan with items.
     */
    public function createLoan(LoanData $data): Loan
    {
        return DB::transaction(function () use ($data) {
            try {
                // Pre-validate availability
                foreach ($data->items as $item) {
                    if ($item->type === LoanItemType::Asset) {
                        $this->validateAssetAvailability($item->asset_id);
                    } elseif ($item->type === LoanItemType::Consumable) {
                        $this->validateStockAvailability($item->consumable_stock_id, $item->quantity_borrowed);
                    }
                }

                $loan = Loan::create([
                    'user_id' => $data->user_id,
                    'borrower_name' => $data->borrower_name,
                    'code' => $data->code,
                    'purpose' => $data->purpose,
                    'loan_date' => $data->loan_date,
                    'due_date' => $data->due_date,
                    'status' => LoanStatus::Pending,
                    'notes' => $data->notes,
                    'proof_image' => $data->proof_image,
                ]);

                foreach ($data->items as $item) {
                    $loan->items()->create([
                        'type' => $item->type,
                        'asset_id' => $item->asset_id,
                        'consumable_stock_id' => $item->consumable_stock_id,
                        'quantity_borrowed' => $item->quantity_borrowed,
                        'quantity_returned' => 0,
                    ]);
                }

                return $loan;

            } catch (LoanException $e) {
                throw $e;
            } catch (Throwable $e) {
                throw LoanException::createFailed($e->getMessage(), $e);
            }
        });
    }

    public function updateLoan(Loan $loan, LoanData $data): Loan
    {
        return DB::transaction(function () use ($loan, $data) {
            if ($loan->status !== LoanStatus::Pending) {
                throw LoanException::updateFailed(__("Cannot edit loan that is not in Pending status."));
            }

            try {
                // Re-validate availability
                foreach ($data->items as $item) {
                    if ($item->type === LoanItemType::Asset) {
                        $this->validateAssetAvailability($item->asset_id);
                    } elseif ($item->type === LoanItemType::Consumable) {
                        $this->validateStockAvailability($item->consumable_stock_id, $item->quantity_borrowed);
                    }
                }

                $updateData = [
                    'user_id' => Auth::id(), // Updater
                    'borrower_name' => $data->borrower_name,
                    'purpose' => $data->purpose,
                    'loan_date' => $data->loan_date,
                    'due_date' => $data->due_date,
                    'notes' => $data->notes,
                ];

                if ($data->proof_image) {
                    $updateData['proof_image'] = $data->proof_image;
                }

                $loan->update($updateData);

                // Sync items by replacing them
                $loan->items()->delete();

                foreach ($data->items as $item) {
                    $loan->items()->create([
                        'type' => $item->type,
                        'asset_id' => $item->asset_id,
                        'consumable_stock_id' => $item->consumable_stock_id,
                        'quantity_borrowed' => $item->quantity_borrowed,
                        'quantity_returned' => 0,
                    ]);
                }

                return $loan;

            } catch (LoanException $e) {
                throw $e;
            } catch (Throwable $e) {
                throw LoanException::updateFailed($e->getMessage(), $e);
            }
        });
    }

    public function approveLoan(Loan $loan): void
    {
        DB::transaction(function () use ($loan) {
            if ($loan->status !== LoanStatus::Pending) {
                throw LoanException::approveFailed(__("Loan status must be pending to approve. Current status: :status", ['status' => $loan->status->value]));
            }

            try {
                foreach ($loan->items as $item) {
                    if ($item->type === LoanItemType::Asset) {
                        $asset = Asset::where('id', $item->asset_id)->lockForUpdate()->first();
                        if ($asset) {
                            if ($asset->status !== AssetStatus::InStock) {
                                throw LoanException::assetUnavailable($asset->asset_tag, $asset->status->getLabel());
                            }
                            $this->assetService->updateStatus($asset, AssetStatus::Loaned, __("Loan Approved: :code", ['code' => $loan->code]));
                        }
                    } elseif ($item->type === LoanItemType::Consumable) {
                        $stock = ConsumableStock::where('id', $item->consumable_stock_id)->lockForUpdate()->first();
                        if ($stock) {
                            if ($stock->quantity < $item->quantity_borrowed) {
                                throw LoanException::insufficientStock($stock->product?->name ?? __('Unknown'), $item->quantity_borrowed, $stock->quantity);
                            }

                            $stockDto = new ConsumableStockData(
                                product_id: $stock->product_id,
                                location_id: $stock->location_id,
                                quantity: $stock->quantity - $item->quantity_borrowed,
                                min_quantity: $stock->min_quantity
                            );

                            $this->stockService->updateStock($stock, $stockDto);
                        }
                    }
                }

                $loan->update(['status' => LoanStatus::Approved]);

            } catch (LoanException $e) {
                throw $e;
            } catch (Throwable $e) {
                throw LoanException::approveFailed($e->getMessage(), $e);
            }
        });
    }

    public function rejectLoan(Loan $loan, ?string $reason = null): void
    {
        if ($loan->status !== LoanStatus::Pending) {
            throw LoanException::rejectFailed(__("Only pending loans can be rejected."));
        }

        try {
            $loan->update([
                'status' => LoanStatus::Rejected,
                'notes' => $reason ? $loan->notes . "\nRejection Reason: " . $reason : $loan->notes
            ]);
        } catch (Throwable $e) {
            throw LoanException::rejectFailed($e->getMessage(), $e);
        }
    }

    public function restoreLoan(Loan $loan): void
    {
        if ($loan->status !== LoanStatus::Rejected) {
            throw LoanException::restoreFailed(__("Only rejected loans can be restored."));
        }

        try {
            $loan->update([
                'status' => LoanStatus::Pending,
                'notes' => $loan->notes . "\n[System] Restored to Pending at " . now()->toDateTimeString(),
            ]);
        } catch (Throwable $e) {
            throw LoanException::restoreFailed($e->getMessage(), $e);
        }
    }

    public function returnItems(Loan $loan, array $returnDetails): void
    {
        DB::transaction(function () use ($loan, $returnDetails) {
            try {
                $hasUpdates = false;

                foreach ($returnDetails as $itemId => $returnData) {
                    // Lock the item row to prevent race conditions during return
                    $item = $loan->items()->where('id', $itemId)->lockForUpdate()->first();

                    if (!$item || $item->loan_id !== $loan->id) {
                        continue;
                    }

                    // Handles Asset Return
                    if ($item->type === LoanItemType::Asset) {
                        if (!empty($returnData['is_returned']) && is_null($item->returned_at)) {
                            $asset = Asset::where('id', $item->asset_id)->lockForUpdate()->first();

                            // Only return if it's currently marked as Loaned
                            if ($asset && $asset->status === AssetStatus::Loaned) {
                                $this->assetService->updateStatus($asset, AssetStatus::InStock, "Returned from Loan: {$loan->code}");
                            }

                            $item->update([
                                'quantity_returned' => 1,
                                'returned_at' => now(),
                            ]);
                            $hasUpdates = true;
                        }
                    }
                    // Handles Consumable Return
                    elseif ($item->type === LoanItemType::Consumable) {
                        $qtyReturning = (int) ($returnData['quantity_returned'] ?? 0);

                        if ($qtyReturning <= 0) {
                            continue;
                        }

                        $remainingToReturn = $item->quantity_borrowed - $item->quantity_returned;

                        // Strict Validation
                        if ($qtyReturning > $remainingToReturn) {
                            throw LoanException::returnFailed(__("Cannot return :qty. Only :remaining remaining for item ID :id.", [
                                'qty' => $qtyReturning,
                                'remaining' => $remainingToReturn,
                                'id' => $itemId
                            ]));
                        }

                        // Restore Stock
                        $stock = ConsumableStock::where('id', $item->consumable_stock_id)->lockForUpdate()->first();
                        if ($stock) {
                            $stockDto = new ConsumableStockData(
                                product_id: $stock->product_id,
                                location_id: $stock->location_id,
                                quantity: $stock->quantity + $qtyReturning,
                                min_quantity: $stock->min_quantity
                            );

                            $this->stockService->updateStock($stock, $stockDto);
                        }

                        // Update Loan Item
                        $item->increment('quantity_returned', $qtyReturning);

                        // If fully returned, mark returned_at
                        if (($item->quantity_returned + $qtyReturning) >= $item->quantity_borrowed) {
                            $item->update(['returned_at' => now()]);
                        } else {
                            // Partial return, still update timestamp for tracking
                             $item->update(['updated_at' => now()]);
                        }

                        $hasUpdates = true;
                    }
                }

                if ($hasUpdates) {
                    $loan->refresh();

                    // Check if ALL items are fully returned
                    $allSettled = $loan->items->every(function ($i) {
                         if ($i->type === LoanItemType::Asset) {
                             return !is_null($i->returned_at);
                         }
                         return $i->quantity_returned >= $i->quantity_borrowed;
                    });

                    if ($allSettled) {
                        $loan->update([
                            'status' => LoanStatus::Closed,
                            'returned_date' => now()
                        ]);
                    }
                }
            } catch (LoanException $e) {
                throw $e;
            } catch (Throwable $e) {
                throw LoanException::returnFailed($e->getMessage(), $e);
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
            throw LoanException::createFailed(__("Asset not found with ID: :id", ['id' => $assetId]));
        }
        if ($asset->status !== AssetStatus::InStock) {
            throw LoanException::assetUnavailable($asset->asset_tag, $asset->status->getLabel());
        }
    }

    protected function validateStockAvailability(?int $stockId, int $qty): void
    {
        if (!$stockId) {
            return;
        }

        $stock = ConsumableStock::find($stockId);
        if (!$stock) {
            throw LoanException::createFailed(__("Stock item not found."));
        }
        if ($stock->quantity < $qty) {
            throw LoanException::insufficientStock($stock->product?->name ?? __('Unknown Item'), $qty, $stock->quantity);
        }
    }

    public function deleteLoan(Loan $loan): void
    {
        if ($loan->status !== LoanStatus::Pending && $loan->status !== LoanStatus::Rejected) {
            throw LoanException::deletionFailed(__("Only Pending or Rejected loans can be deleted."));
        }

        DB::transaction(function () use ($loan) {
            try {
                $loan->items()->delete();
                $loan->delete();
            } catch (Throwable $e) {
                throw LoanException::deletionFailed($e->getMessage(), $e);
            }
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
