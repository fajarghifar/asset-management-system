<?php
namespace App\Services;

use App\Enums\ItemType;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\InventoryItem;
use App\Enums\InventoryStatus;
use Illuminate\Validation\ValidationException;

class BorrowingService
{
    public function processItems(Borrowing $borrowing, array $itemsData): void
    {
        foreach ($itemsData as $row) {
            if (empty($row['inventory_item_id']))
                continue;

            $inventoryItem = InventoryItem::findOrFail($row['inventory_item_id']);
            $item = $inventoryItem->item;

            if ($item->type === ItemType::Fixed) {
                if ($inventoryItem->status !== InventoryStatus::Available) {
                    throw ValidationException::withMessages([
                        'items' => "Unit aset '{$item->name}' tidak tersedia (status: {$inventoryItem->status->getLabel()})."
                    ]);
                }

                BorrowingItem::create([
                    'borrowing_id' => $borrowing->id,
                    'inventory_item_id' => $inventoryItem->id,
                    'quantity' => 1,
                ]);

            } else { // Consumable
                $reqQty = $row['quantity'] ?? 1;
                if ($inventoryItem->quantity < $reqQty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok '{$item->name}' tidak mencukupi (tersedia: {$inventoryItem->quantity})."
                    ]);
                }

                BorrowingItem::create([
                    'borrowing_id' => $borrowing->id,
                    'inventory_item_id' => $inventoryItem->id,
                    'quantity' => $reqQty,
                ]);
            }
        }
    }
}
