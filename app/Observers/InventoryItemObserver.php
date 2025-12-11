<?php

namespace App\Observers;

use App\Models\Item;
use App\Enums\ItemType;
use Illuminate\Support\Str;
use App\Models\InventoryItem;
use App\Enums\InventoryStatus;
use Illuminate\Validation\ValidationException;

class InventoryItemObserver
{
    public function creating(InventoryItem $inventory): void
    {
        $item = $inventory->item ?? Item::find($inventory->item_id);

        if (!$item) {
            throw ValidationException::withMessages(['item_id' => 'Master Item tidak ditemukan.']);
        }

        if (!in_array($item->type, [ItemType::Fixed, ItemType::Consumable])) {
            throw ValidationException::withMessages([
                'item_id' => "Item '{$item->name}' tidak dapat dimasukkan ke Inventory (hanya Fixed & Consumable)."
            ]);
        }

        if (empty($inventory->location_id)) {
            throw ValidationException::withMessages(['location_id' => 'Lokasi wajib diisi.']);
        }

        if (empty($inventory->code)) {
            $prefix = $item->code;
            $dateCode = now()->format('ymd');

            do {
                $randomSuffix = strtoupper(Str::random(4));
                $generatedCode = "{$prefix}-{$dateCode}-{$randomSuffix}";
            } while (InventoryItem::where('code', $generatedCode)->exists());

            $inventory->code = $generatedCode;
        }

        if ($item->type === ItemType::Fixed) {
            $inventory->quantity = 1;
        }
    }

    public function saving(InventoryItem $inventory): void
    {
        if (!$inventory->relationLoaded('item')) {
            $inventory->load('item');
        }

        $item = $inventory->item;

        if (!in_array($item->type, [ItemType::Fixed, ItemType::Consumable])) {
            throw ValidationException::withMessages([
                'item_id' => "Item dengan tipe '{$item->type}' tidak diizinkan di InventoryItem."
            ]);
        }

        if ($item->type === ItemType::Fixed) {
            $inventory->quantity = 1;

            if (empty($inventory->location_id)) {
                throw ValidationException::withMessages([
                    'location_id' => 'Lokasi wajib diisi untuk Aset Tetap.'
                ]);
            }
        } else {
            if ($inventory->quantity < 0) {
                throw ValidationException::withMessages(['quantity' => 'Stok tidak boleh negatif.']);
            }
        }
    }

    public function deleting(InventoryItem $inventory): void
    {
        if (!$inventory->isForceDeleting()) {
            $item = $inventory->item ?? Item::find($inventory->item_id);

            if ($item->type === ItemType::Consumable) {
                if ($inventory->quantity > 0) {
                    throw ValidationException::withMessages([
                        'quantity' => "Gagal Hapus: Masih ada sisa stok ({$inventory->quantity} unit)."
                    ]);
                }
            } else {
                if ($inventory->status === InventoryStatus::Borrowed) {
                    throw ValidationException::withMessages([
                        'status' => 'Gagal Hapus: Aset ini sedang dipinjam.'
                    ]);
                }
            }
        }
    }
}
