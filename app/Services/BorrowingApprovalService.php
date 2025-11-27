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

class BorrowingApprovalService
{
    public function approve(Borrowing $borrowing): void
    {
        if ($borrowing->status !== BorrowingStatus::Pending) {
            return;
        }

        DB::transaction(function () use ($borrowing) {
            foreach ($borrowing->items as $borrowingItem) {
                $this->processApprovalItem($borrowingItem);
            }

            $borrowing->update([
                'status' => BorrowingStatus::Approved,
                'notes' => $borrowing->notes . "\n[System] Disetujui pada " . now()->format('d M Y H:i'),
            ]);
        });
    }

    public function reject(Borrowing $borrowing, string $reason): void
    {
        if ($borrowing->status !== BorrowingStatus::Pending) {
            return;
        }

        $borrowing->update([
            'status' => BorrowingStatus::Rejected,
            'notes' => $borrowing->notes . "\n[System] Ditolak: $reason",
        ]);
    }

    private function processApprovalItem(BorrowingItem $borrowingItem): void
    {
        $item = $borrowingItem->item;

        if ($item->type === ItemType::Consumable) {
            $this->deductStock($borrowingItem);
        } elseif ($item->type === ItemType::Fixed) {
            $this->markInstanceAsBorrowed($borrowingItem);
        }
    }

    private function deductStock(BorrowingItem $borrowingItem): void
    {
        $stock = ItemStock::where('item_id', $borrowingItem->item_id)
            ->where('location_id', $borrowingItem->location_id)
            ->lockForUpdate()
            ->first();

        if (!$stock || $stock->quantity < $borrowingItem->quantity) {
            throw ValidationException::withMessages([
                'error' => "Gagal menyetujui. Stok {$borrowingItem->item->name} tidak mencukupi saat ini."
            ]);
        }

        $stock->decrement('quantity', $borrowingItem->quantity);
    }

    private function markInstanceAsBorrowed(BorrowingItem $borrowingItem): void
    {
        $instance = $borrowingItem->fixedInstance()
            ->lockForUpdate()
            ->first();

        if ($instance->status !== FixedItemStatus::Available) {
            throw ValidationException::withMessages([
                'error' => "Unit {$instance->code} sudah tidak tersedia (mungkin baru saja dipinjam/rusak)."
            ]);
        }

        $instance->update(['status' => FixedItemStatus::Borrowed]);
    }
}
