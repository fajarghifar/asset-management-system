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
        (new InstalledItemInstanceService())->validate($instance);
    }

    public function saved(InstalledItemInstance $instance)
    {
        (new InstalledItemInstanceService())->save($instance);
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
