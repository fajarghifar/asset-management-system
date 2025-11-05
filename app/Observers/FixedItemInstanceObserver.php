<?php

namespace App\Observers;

use App\Models\FixedItemInstance;
use App\Services\FixedItemInstanceService;

class FixedItemInstanceObserver
{
    public function saving(FixedItemInstance $instance)
    {
        (new FixedItemInstanceService())->validate($instance);
    }

    public function restored(FixedItemInstance $instance)
    {
        (new FixedItemInstanceService())->restore($instance);
    }

    public function forceDeleted(FixedItemInstance $instance)
    {
        (new FixedItemInstanceService())->forceDelete($instance);
    }
}
