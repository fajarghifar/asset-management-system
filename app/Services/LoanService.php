<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\Asset;
use App\Models\LoanItem;
use App\Enums\LoanStatus;
use App\Enums\AssetStatus;
use App\Enums\ProductType;
use Illuminate\Support\Str;
use App\Models\ConsumableStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoanService
{
    /**
     * Create a new loan with auto-generated code.
     */
    public function createLoan(array $data, array $items): Loan
    {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'loanItems' => 'Peminjaman harus memiliki minimal satu barang.'
            ]);
        }

        return DB::transaction(function () use ($data, $items) {
            $data['code'] = $this->generateUniqueCode();
            $data['status'] = LoanStatus::Pending;

            $loan = Loan::create($data);

            foreach ($items as $item) {
                $loan->loanItems()->create([
                    'type' => $item['type'],
                    'asset_id' => $item['asset_id'] ?? null,
                    'consumable_stock_id' => $item['consumable_stock_id'] ?? null,
                    'quantity_borrowed' => $item['quantity_borrowed'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            Log::info("Loan Created: {$loan->code} by User " . Auth::id());
            return $loan;
        });
    }

    /**
     * Approve Loan: Lock rows, validate stock/status, deduct inventory.
     */
    public function approveLoan(Loan $loan): void
    {
        if ($loan->status !== LoanStatus::Pending) {
            throw ValidationException::withMessages(['status' => 'Only User Pending loans can be approved.']);
        }

        DB::transaction(function () use ($loan) {
            $loan->refresh();
            $assetService = app(AssetService::class);

            foreach ($loan->loanItems as $item) {
                if ($item->type === ProductType::Consumable) {
                    $stock = ConsumableStock::lockForUpdate()->find($item->consumable_stock_id);

                    if (!$stock || $stock->quantity < $item->quantity_borrowed) {
                        throw ValidationException::withMessages([
                            'error' => "Stok tidak mencukupi untuk item: {$item->product_name}"
                        ]);
                    }

                    $stock->decrement('quantity', $item->quantity_borrowed);
                } else {
                    $asset = Asset::lockForUpdate()->find($item->asset_id);

                    if (!$asset || $asset->status !== AssetStatus::InStock) {
                        throw ValidationException::withMessages([
                            'error' => "Aset {$asset->asset_tag} tidak tersedia (Status: {$asset->status->getLabel()})"
                        ]);
                    }

                    // Use AssetService to check out and log history
                    try {
                        $assetService->checkOut(
                            $asset,
                            $loan->borrower_name,
                            "Peminjaman: {$loan->code}"
                        );
                    } catch (\Exception $e) {
                        throw ValidationException::withMessages(['error' => $e->getMessage()]);
                    }
                }
            }

            $loan->update([
                'status' => LoanStatus::Approved,
                'notes' => $loan->notes . "\n[System] Approved at " . now()->toDateTimeString(),
            ]);
        });
    }

    /**
     * Process Return: Partial or Full.
     */
    public function returnItems(Loan $loan, array $returnItemsData): void
    {
        DB::transaction(function () use ($loan, $returnItemsData) {
            $assetService = app(AssetService::class);

            foreach ($returnItemsData as $data) {
                $itemId = $data['loan_item_id'] ?? $data['id'] ?? null;
                if (!$itemId) continue;

                $loanItem = LoanItem::lockForUpdate()->find($itemId);

                if (!$loanItem) continue;

                $qtyToReturn = (int) ($data['return_quantity'] ?? 0);

                if ($loanItem->type === ProductType::Asset) {
                    // If Asset, check toggle. If false, skip processing.
                    if (empty($data['is_returning']))
                        continue;
                    $qtyToReturn = 1;
                }

                // For Consumables, we allow returning 0 (meaning all used up), so we don't skip if 0.
                if ($loanItem->type === ProductType::Asset && $qtyToReturn <= 0)
                    continue;

                if ($loanItem->type === ProductType::Consumable) {
                    // Only restore stock if actually returning something
                    if ($loanItem->consumableStock && $qtyToReturn > 0) {
                        $loanItem->consumableStock()->increment('quantity', $qtyToReturn);
                    }
                    // Consumables are considered "resolved" once any return action is processed
                    $loanItem->returned_at = now();
                } else {
                    if ($loanItem->asset_id) {
                        $asset = Asset::lockForUpdate()->find($loanItem->asset_id);
                        if ($asset && $asset->status === AssetStatus::Loaned) {
                            // Use AssetService to check in and log history
                            try {
                                $assetService->checkIn(
                                    $asset,
                                    $asset->location_id, // Return to original location
                                    "Pengembalian Peminjaman: {$loan->code}"
                                );
                            } catch (\Exception $e) {
                                // Ignore if already in stock or other minor issues, but log
                                Log::warning("Asset CheckIn Warning: " . $e->getMessage());
                                $asset->update(['status' => AssetStatus::InStock]);
                            }
                        }
                    }
                }

                if ($qtyToReturn > 0) {
                    $loanItem->quantity_returned += $qtyToReturn;
                }

                // For Assets, mark returned if satisfied (should be 1)
                if ($loanItem->type === ProductType::Asset && $loanItem->quantity_returned >= $loanItem->quantity_borrowed) {
                    $loanItem->returned_at = now();
                }

                $loanItem->save();
            }
            $this->checkLoanCompletion($loan);
        });
    }

    public function rejectLoan(Loan $loan, string $reason): void
    {
        if ($loan->status !== LoanStatus::Pending) return;

        $loan->update([
            'status' => LoanStatus::Rejected,
            'notes' => $loan->notes . "\n[System] Rejected: $reason",
        ]);
    }

    private function checkLoanCompletion(Loan $loan): void
    {
        $loan->refresh();
        // Check if all items are marked as returned (returned_at IS NOT NULL)
        $allReturned = $loan->loanItems->every(function ($item) {
            return !is_null($item->returned_at);
        });

        if ($allReturned && $loan->status !== LoanStatus::Closed) {
            $loan->update([
                'status' => LoanStatus::Closed,
                'returned_date' => now(),
            ]);
        }
    }

    private function generateUniqueCode(): string
    {
        // Format: B[YY][MM][DD][XXX] -> e.g. B260101ABC
        $prefix = 'B' . date('ymd');

        do {
            $random = strtoupper(Str::random(3));
            $code = $prefix . $random;
        } while (Loan::where('code', $code)->exists());

        return $code;
    }
}
