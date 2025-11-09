<?php

namespace App\Services;

use App\Models\InstalledItemInstance;
use App\Models\InstalledItemLocationHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InstalledItemInstanceService
{
    /**
     * Validasi hanya perubahan lokasi (bukan seluruh instance).
     */
    public function validateLocationChange(InstalledItemInstance $instance): void
    {
        // Cek hanya jika lokasi berubah
        if (!$instance->isDirty('installed_location_id')) {
            return;
        }
        // Pastikan item tetap tipe installed
        if ($instance->item->type !== 'installed') {
            throw ValidationException::withMessages([
                'item_id' => 'Hanya Barang Terpasang yang bisa dibuat instance.',
            ]);
        }
    }

    /**
     * Buat riwayat awal saat instance pertama kali dibuat.
     */
    public function createInitialHistory(InstalledItemInstance $instance): void
    {
        InstalledItemLocationHistory::create([
            'instance_id' => $instance->id,
            'location_id' => $instance->installed_location_id,
            'installed_at' => $instance->installed_at,
            'notes' => 'Pemasangan awal',
        ]);
    }

    /**
     * Buat riwayat baru saat lokasi berubah.
     */
    public function createLocationHistory(InstalledItemInstance $instance): void
    {
        // Tutup riwayat lama
        InstalledItemLocationHistory::where('instance_id', $instance->id)
            ->whereNull('removed_at')
            ->update(['removed_at' => now()]);
        // Buat riwayat baru
        InstalledItemLocationHistory::create([
            'instance_id' => $instance->id,
            'location_id' => $instance->installed_location_id,
            'installed_at' => now(),
            'notes' => 'Pindah lokasi',
        ]);
    }

    public function delete(InstalledItemInstance $instance): void
    {
        $instance->delete();
    }

    public function restore(InstalledItemInstance $instance): void
    {
        $instance->restore();
    }

    public function forceDelete(InstalledItemInstance $instance): void
    {
        $instance->forceDelete();
    }
}
