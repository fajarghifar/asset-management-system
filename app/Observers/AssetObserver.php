<?php

namespace App\Observers;

use App\Models\Asset;
use App\Enums\AssetAction;
use App\Models\AssetHistory;
use Illuminate\Support\Facades\Auth;

class AssetObserver
{
    /**
     * Handle the Asset "created" event.
     */
    public function created(Asset $asset): void
    {
        $this->logHistory(
            $asset,
            AssetAction::Register,
            'Aset baru berhasil didaftarkan ke dalam sistem.'
        );
    }

    /**
     * Handle the Asset "updated" event.
     * Logs changes only for specific administrative fields.
     */
    public function updated(Asset $asset): void
    {
        // Skip logging if the flag is explicitly set to false (e.g. during specific Service operations)
        if (! $asset->shouldLogHistory) {
            return;
        }

        $ignoredColumns = [
            'updated_at',
            'created_at',
            'deleted_at',
            'status',
            'location_id',
            'product_id',
        ];

        $dirtyAttributes = array_keys($asset->getDirty());

        $trackedChanges = array_diff($dirtyAttributes, $ignoredColumns);

        if (!empty($trackedChanges)) {
            $changesList = implode(', ', $trackedChanges);

            $this->logHistory(
                $asset,
                AssetAction::Update,
                "Pembaruan data administratif pada kolom: [{$changesList}]."
            );
        }
    }

    private function logHistory(Asset $asset, AssetAction $action, string $note): void
    {
        AssetHistory::create([
            'asset_id' => $asset->id,
            'user_id' => Auth::id(),
            'status' => $asset->status,
            'location_id' => $asset->location_id,
            'action_type' => $action,
            'notes' => $note,
        ]);
    }
}
