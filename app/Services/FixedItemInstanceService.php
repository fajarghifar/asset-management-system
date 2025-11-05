<?php

namespace App\Services;

use App\Models\FixedItemInstance;
use Illuminate\Validation\ValidationException;

class FixedItemInstanceService
{
    /**
     * Validasi saat membuat/mengupdate instance.
     */
    public function validate(FixedItemInstance $instance): void
    {
        // Pastikan item-nya benar-benar tipe 'fixed'
        if ($instance->item->type !== 'fixed') {
            throw ValidationException::withMessages([
                'item_id' => 'Item yang dipilih bukan Barang Tetap.',
            ]);
        }

        // Status 'borrowed' atau 'maintenance' tidak boleh punya lokasi
        if (in_array($instance->status, ['borrowed', 'maintenance']) && $instance->current_location_id) {
            throw ValidationException::withMessages([
                'current_location_id' => 'Instance dengan status Dipinjam/Perawatan tidak boleh memiliki lokasi.',
            ]);
        }

        // Status 'available' wajib punya lokasi
        if ($instance->status === 'available' && !$instance->current_location_id) {
            throw ValidationException::withMessages([
                'current_location_id' => 'Instance Tersedia wajib memiliki lokasi.',
            ]);
        }
    }

    /**
     * Soft delete instance.
     */
    public function delete(FixedItemInstance $instance): void
    {
        // Jangan izinkan hapus jika sedang dipinjam
        if ($instance->status === 'borrowed') {
            throw ValidationException::withMessages([
                'instance' => 'Tidak bisa menghapus: instance ini sedang dipinjam.',
            ]);
        }

        $instance->delete();
    }

    /**
     * Restore instance.
     */
    public function restore(FixedItemInstance $instance): void
    {
        $instance->restore();
    }

    /**
     * Force delete instance.
     */
    public function forceDelete(FixedItemInstance $instance): void
    {
        $instance->forceDelete();
    }
}
