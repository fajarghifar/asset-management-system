<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ItemManagementService
{
    /**
     * Validasi: jangan izinkan ubah tipe jika item sudah punya data terkait.
     */
    public function validateTypeChange(Item $item, string $newType): void
    {
        if ($item->type === $newType) {
            return;
        }

        // Cek apakah ada data terkait di tabel yang relevan
        $hasRelatedData = match ($item->type) {
            'consumable' => $item->stocks()->exists(),
            'fixed' => $item->fixedInstances()->exists(),
            'installed' => $item->installedInstances()->exists(),
            default => false,
        };

        if ($hasRelatedData) {
            $label = match ($item->type) {
                'consumable' => 'Barang Habis Pakai',
                'fixed' => 'Barang Tetap',
                'installed' => 'Barang Terpasang',
                default => 'barang',
            };
            throw ValidationException::withMessages([
                'type' => "Tidak bisa mengubah tipe: item ini sudah memiliki data sebagai {$label}.",
            ]);
        }
    }

    /**
     * Periksa apakah item bisa dihapus.
     */
    public function canDelete(Item $item): bool
    {
        return match ($item->type) {
            'consumable' => $item->stocks()->sum('quantity') === 0,
            'fixed' => $item->fixedInstances()->where('status', '!=', 'available')->count() === 0,
            'installed' => true,
            default => false,
        };
    }

    /**
     * Dapatkan pesan error jika tidak bisa dihapus.
     */
    public function getErrorMessage(Item $item): string
    {
        return match ($item->type) {
            'consumable' => "Tidak bisa menghapus: masih memiliki total stok {$item->stocks()->sum('quantity')} unit.",
            'fixed' => "Tidak bisa menghapus: masih memiliki " . $item->fixedInstances()->where('status', '!=', 'available')->count() . " instance yang aktif (dipinjam/perawatan).",
            'installed' => 'Tidak bisa menghapus: item ini memiliki instance terpasang.',
            default => 'Tipe barang tidak valid.',
        };
    }

    /**
     * Soft delete item + semua data terkait.
     */
    public function delete(Item $item): void
    {
        if (!$this->canDelete($item)) {
            throw ValidationException::withMessages([
                'item' => $this->getErrorMessage($item),
            ]);
        }

        DB::transaction(function () use ($item) {
            match ($item->type) {
                'consumable' => $item->stocks()->each->delete(),
                'fixed' => $item->fixedInstances()->each->delete(),
                'installed' => $item->installedInstances()->each->delete(),
            };
            $item->delete();
        });
    }

    /**
     * Restore item + semua data terkait.
     */
    public function restore(Item $item): void
    {
        DB::transaction(function () use ($item) {
            match ($item->type) {
                'consumable' => $item->stocks()->withTrashed()->each->restore(),
                'fixed' => $item->fixedInstances()->withTrashed()->each->restore(),
                'installed' => $item->installedInstances()->withTrashed()->each->restore(),
            };
            $item->restore();
        });
    }

    /**
     * Force delete (hapus permanen) item + semua data terkait.
     */
    public function forceDelete(Item $item): void
    {
        DB::transaction(function () use ($item) {
            match ($item->type) {
                'consumable' => $item->stocks()->withTrashed()->forceDelete(),
                'fixed' => $item->fixedInstances()->withTrashed()->forceDelete(),
                'installed' => $item->installedInstances()->withTrashed()->forceDelete(),
            };
            $item->forceDelete();
        });
    }
}
