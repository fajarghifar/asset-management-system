<?php

namespace App\Observers;

use App\Models\Item;
use App\Services\ItemManagementService;

class ItemObserver
{
    public function saving(Item $item)
    {
        if ($item->exists && $item->isDirty('type')) {
            (new ItemManagementService())->validateTypeChange($item, $item->type);
        }
    }

    public function restored(Item $item)
    {
        (new ItemManagementService())->restore($item);
    }

    public function forceDeleted(Item $item)
    {
        (new ItemManagementService())->forceDelete($item);
    }
}
