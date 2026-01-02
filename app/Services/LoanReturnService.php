<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanItem;
use App\Enums\LoanStatus;
use App\Enums\ProductType;
use App\Enums\AssetStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanReturnService
{
    /**
     * Process logic for returning items (partial or full).
     */
    public function processReturn(Loan $loan, LoanItem $loanItem, int $stockIncrement, int $resolutionIncrement, bool $isResolved = false, ?string $conditionNotes = null): void
    {
        DB::transaction(function () use ($loan, $loanItem, $stockIncrement, $resolutionIncrement, $isResolved, $conditionNotes) {

            // Handle Inventory Updates
            if ($loanItem->type === ProductType::Consumable) {
                if ($stockIncrement > 0) {
                    $loanItem->consumableStock->increment('quantity', $stockIncrement);
                }
            } else {
                // For assets, set status back to InStock if physically returned
                if ($stockIncrement > 0 && $loanItem->asset_id) {
                    $loanItem->asset->update([
                        'status' => AssetStatus::InStock,
                        // Logic for location assignment can be refined here if needed
                    ]);
                }
            }

            // Update Loan Item Data (Resolution)
            if ($resolutionIncrement > 0) {
                $loanItem->increment('quantity_returned', $resolutionIncrement);
            }

            // Mark completion if explicitly resolved OR fully returned
            if ($isResolved || $loanItem->quantity_returned >= $loanItem->quantity_borrowed) {
                if ($loanItem->returned_at === null) {
                    $loanItem->update(['returned_at' => now()]);
                }
            }

            // Check if the entire Loan is completed
            $this->checkLoanCompletion($loan);
        });
    }

    /**
     * Check if all items in a loan have been returned, and close the loan if so.
     */
    private function checkLoanCompletion(Loan $loan): void
    {
        $loan->refresh(); // Load fresh relation data

        // Check completion based on returned_at timestamp (explicit resolution)
        $allReturned = $loan->loanItems->every(
            fn($item) => $item->returned_at !== null
        );

        if ($allReturned && $loan->status !== LoanStatus::Closed) {
            $loan->update([
                'status' => LoanStatus::Closed,
                'returned_date' => now(),
            ]);
        }
    }
}
