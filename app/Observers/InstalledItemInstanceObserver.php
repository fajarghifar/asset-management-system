<?php

namespace App\Observers;

use App\Models\InstalledItemInstance;
use App\Services\InstalledItemInstanceService;

class InstalledItemInstanceObserver
{
    protected InstalledItemInstanceService $service;

    public function __construct()
    {
        $this->service = new InstalledItemInstanceService();
    }

    public function saving(InstalledItemInstance $instance)
    {
        // Hanya validasi jika ini update (bukan create)
        if ($instance->exists) {
            $this->service->validateLocationChange($instance);
        }
    }

    public function saved(InstalledItemInstance $instance)
    {
        // Hanya buat riwayat jika ini create, atau lokasi berubah
        if (!$instance->wasRecentlyCreated) {
            if ($instance->isDirty('installed_location_id')) {
                $this->service->createLocationHistory($instance);
            }
        } else {
            $this->service->createInitialHistory($instance);
        }
    }

    public function restored(InstalledItemInstance $instance)
    {
        $this->service->restore($instance);
    }

    public function forceDeleted(InstalledItemInstance $instance)
    {
        $this->service->forceDelete($instance);
    }
}
