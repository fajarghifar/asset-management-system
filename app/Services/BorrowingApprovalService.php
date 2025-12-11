<?php
namespace App\Services;

use App\Enums\ItemType;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Enums\BorrowingStatus;
use App\Enums\InventoryStatus;
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
            foreach ($borrowing->items->load('inventoryItem.item') as $borrowingItem) {
                $this->processApprovalItem($borrowingItem);
            }

            $borrowing->update([
                'status' => BorrowingStatus::Approved,
                'notes' => trim($borrowing->notes . "\n[Sistem] Disetujui pada " . now()->format('d M Y H:i')),
            ]);
        });
    }

    private function processApprovalItem(BorrowingItem $borrowingItem): void
    {
        $inventoryItem = $borrowingItem->inventoryItem;
        $item = $inventoryItem->item;

        if ($item->type === ItemType::Consumable) {
            if ($inventoryItem->quantity < $borrowingItem->quantity) {
                throw ValidationException::withMessages([
                    'error' => "Stok '{$item->name}' tidak mencukupi."
                ]);
            }
            $inventoryItem->decrement('quantity', $borrowingItem->quantity);
        } else {
            if ($inventoryItem->status !== InventoryStatus::Available) {
                throw ValidationException::withMessages([
                    'error' => "Aset '{$inventoryItem->code}' sedang dipinjam."
                ]);
            }
            $inventoryItem->update(['status' => InventoryStatus::Borrowed]);
        }

        $borrowingItem->update(['status' => 'borrowed']);
    }

    public function reject(Borrowing $borrowing, string $reason): void
    {
        if ($borrowing->status !== BorrowingStatus::Pending) {
            return;
        }

        $borrowing->update([
            'status' => BorrowingStatus::Rejected,
            'notes' => trim($borrowing->notes . "\n[Sistem] Ditolak: $reason"),
        ]);
    }
}
