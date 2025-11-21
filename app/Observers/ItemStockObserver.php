<?php

namespace App\Observers;

use App\Enums\ItemType;
use App\Models\ItemStock;
use Illuminate\Validation\ValidationException;

class ItemStockObserver
{
    /**
     * Handle the ItemStock "saving" event.
     */
    public function saving(ItemStock $stock): void
    {
        if ($stock->item && $stock->item->type !== ItemType::Consumable) {
            throw ValidationException::withMessages([
                'item_id' => 'Stok hanya bisa ditambahkan untuk Barang Habis Pakai (Consumable).',
            ]);
        }

        if ($stock->quantity < 0) {
            throw ValidationException::withMessages(['quantity' => 'Stok tidak boleh negatif.']);
        }
    }

    /**
     * Handle the ItemStock "deleting" event.
     */
    public function deleting(ItemStock $stock): void
    {
        if (!$stock->isForceDeleting()) {
            if ($stock->quantity > 0) {
                throw ValidationException::withMessages([
                    'quantity' => "Gagal Hapus: Masih ada sisa stok ({$stock->quantity} unit) di lokasi ini. Nol-kan stok terlebih dahulu.",
                ]);
            }
        }
    }
}
