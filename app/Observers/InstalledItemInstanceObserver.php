<?php

namespace App\Observers;

use App\Enums\ItemType;
use App\Models\InstalledItemInstance;
use App\Models\InstalledItemLocationHistory;
use Illuminate\Validation\ValidationException;

class InstalledItemInstanceObserver
{
    public function saving(InstalledItemInstance $instance): void
    {
        if ($instance->exists && $instance->isDirty('current_location_id')) {
            if (!$instance->relationLoaded('item')) {
                $instance->load('item');
            }

            if ($instance->item->type !== ItemType::Installed) {
                throw ValidationException::withMessages([
                    'item_id' => 'Hanya Barang tipe Terpasang yang boleh dipindahkan lokasinya.',
                ]);
            }
        }
    }

    public function saved(InstalledItemInstance $instance): void
    {
        if ($instance->wasRecentlyCreated) {
            InstalledItemLocationHistory::create([
                'instance_id' => $instance->id,
                'location_id' => $instance->current_location_id,
                'installed_at' => $instance->installed_at,
                'notes' => 'Pemasangan awal',
            ]);
        } elseif ($instance->isDirty('current_location_id')) {
            InstalledItemLocationHistory::where('instance_id', $instance->id)
                ->whereNull('removed_at')
                ->update(['removed_at' => now()]);

            InstalledItemLocationHistory::create([
                'instance_id' => $instance->id,
                'location_id' => $instance->current_location_id,
                'installed_at' => now(),
                'notes' => 'Pindah lokasi',
            ]);
        }
    }

    public function deleted(InstalledItemInstance $instance): void
    {
        InstalledItemLocationHistory::where('instance_id', $instance->id)
            ->whereNull('removed_at')
            ->update([
                'removed_at' => now(),
                'notes' => 'Barang dinonaktifkan/dihapus dari sistem'
            ]);
    }
}
