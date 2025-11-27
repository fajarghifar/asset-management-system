<?php

namespace App\Services;

use App\Enums\ItemType;
use App\Models\Borrowing;
use App\Models\ItemStock;
use App\Models\BorrowingItem;
use App\Enums\BorrowingStatus;
use App\Enums\FixedItemStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BorrowingReturnService
{
    public function processReturn(BorrowingItem $borrowingItem, int $returnQty, ?string $conditionNotes = null): void
    {
        if ($returnQty <= 0) {
            throw ValidationException::withMessages(['quantity' => 'Jumlah kembali harus > 0']);
        }

        $remaining = $borrowingItem->quantity - $borrowingItem->returned_quantity;
        if ($returnQty > $remaining) {
            throw ValidationException::withMessages(['quantity' => "Maksimal pengembalian untuk item ini adalah {$remaining}."]);
        }

        DB::transaction(function () use ($borrowingItem, $returnQty, $conditionNotes) {
            if ($borrowingItem->item->type === ItemType::Consumable) {
                $this->restoreStock($borrowingItem, $returnQty);
            } elseif ($borrowingItem->item->type === ItemType::Fixed) {
                $this->restoreInstance($borrowingItem, $conditionNotes);
            }

            $borrowingItem->increment('returned_quantity', $returnQty);
            $borrowingItem->update(['returned_at' => now()]);

            $this->checkBorrowingCompletion($borrowingItem->borrowing);
        });
    }

    private function restoreStock(BorrowingItem $borrowingItem, int $qty): void
    {
        $stock = ItemStock::firstOrCreate(
            ['item_id' => $borrowingItem->item_id, 'location_id' => $borrowingItem->location_id],
            ['quantity' => 0, 'min_quantity' => 0]
        );
        $stock->increment('quantity', $qty);
    }

    private function restoreInstance(BorrowingItem $borrowingItem, ?string $notes): void
    {
        $status = FixedItemStatus::Available;

        $borrowingItem->fixedInstance()->update([
            'status' => $status,
            'notes' => $notes
        ]);
    }

    private function checkBorrowingCompletion(Borrowing $borrowing): void
    {
        $borrowing->refresh();

        $isAllReturned = $borrowing->items->every(function ($item) {
            return $item->returned_quantity >= $item->quantity;
        });

        if ($isAllReturned) {
            $borrowing->update([
                'status' => BorrowingStatus::Completed,
                'actual_return_date' => now()
            ]);
        }
    }
}
