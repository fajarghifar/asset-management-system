<?php
namespace App\Services;

use App\Models\Item;
use App\Enums\ItemType;
use App\Models\Borrowing;
use App\Models\ItemStock;
use App\Models\BorrowingItem;
use App\Enums\BorrowingStatus;
use App\Enums\FixedItemStatus;
use App\Models\FixedItemInstance;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BorrowingService
{
    public function create(array $headerData, array $items): Borrowing
    {
        return DB::transaction(function () use ($headerData, $items) {
            // 1. Create Header
            $borrowing = Borrowing::create([
                'code' => $this->generateCode(),
                'user_id' => $headerData['user_id'],
                'purpose' => $headerData['purpose'],
                'borrow_date' => $headerData['borrow_date'],
                'expected_return_date' => $headerData['expected_return_date'],
                'status' => BorrowingStatus::Pending,
                'notes' => $headerData['notes'] ?? null,
            ]);

            // 2. Process Items
            foreach ($items as $row) {
                $this->processItemRow($borrowing, $row);
            }

            return $borrowing;
        });
    }

    private function processItemRow(Borrowing $borrowing, array $row): void
    {
        $item = Item::find($row['item_id']);

        if (!$item) {
            throw ValidationException::withMessages(['items' => "Item ID {$row['item_id']} tidak ditemukan."]);
        }

        // Validasi stok/ketersediaan (Double check di sisi server)
        if ($item->type === ItemType::Fixed) {
            $instance = FixedItemInstance::where('id', $row['fixed_instance_id'])
                ->where('status', FixedItemStatus::Available)
                ->first();

            if (!$instance) {
                throw ValidationException::withMessages(['items' => "Unit {$item->name} yang dipilih tidak tersedia."]);
            }
        } elseif ($item->type === ItemType::Consumable) {
            $stock = ItemStock::where('item_id', $item->id)
                ->where('location_id', $row['location_id'])
                ->first();

            if (!$stock || $stock->quantity < $row['quantity']) {
                throw ValidationException::withMessages(['items' => "Stok {$item->name} tidak mencukupi."]);
            }
        }

        // Simpan Item
        BorrowingItem::create([
            'borrowing_id' => $borrowing->id,
            'item_id' => $item->id,
            'fixed_instance_id' => $row['fixed_instance_id'] ?? null,
            'location_id' => $row['location_id'] ?? null,
            'quantity' => $item->type === ItemType::Fixed ? 1 : ($row['quantity'] ?? 1),
            'status' => 'borrowed',
        ]);
    }

    private function generateCode(): string
    {
        $prefix = 'BRW-' . date('Ymd') . '-';
        $last = Borrowing::where('code', 'like', $prefix . '%')->max('code');
        $num = $last ? (int) substr($last, -4) + 1 : 1;
        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
