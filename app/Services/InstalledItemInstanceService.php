<?php

namespace App\Services;

use App\Models\InstalledItemInstance;
use App\Models\InstalledItemLocationHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InstalledItemInstanceService
{
    public function validate(InstalledItemInstance $instance): void
    {
        if ($instance->item->type !== 'installed') {
            throw ValidationException::withMessages([
                'item_id' => 'Hanya Barang Terpasang yang bisa dibuat instance.',
            ]);
        }
    }

    public function save(InstalledItemInstance $instance): void
    {
        DB::transaction(function () use ($instance) {
            $isNew = $instance->id === null;
            $instance->save();

            if ($isNew) {
                // Riwayat awal saat instance dibuat
                InstalledItemLocationHistory::create([
                    'instance_id' => $instance->id,
                    'location_id' => $instance->installed_location_id,
                    'installed_at' => $instance->installed_at,
                    'notes' => 'Pemasangan awal',
                ]);
            } else {
                // Cek perubahan lokasi
                $originalLocationId = $instance->getOriginal('installed_location_id');
                if ($originalLocationId && $originalLocationId !== $instance->installed_location_id) {
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
            }
        });
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
