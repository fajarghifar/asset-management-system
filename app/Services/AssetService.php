<?php

namespace App\Services;

use App\Models\Asset;
use App\Enums\AssetAction;
use App\Enums\AssetStatus;
use App\Models\AssetHistory;
use Illuminate\Support\Facades\Auth;

class AssetService
{
    /**
     * Move asset to a new location.
     */
    public function move(Asset $asset, int $locationId, string $notes): void
    {
        $asset->shouldLogHistory = false;
        $asset->update(['location_id' => $locationId]);

        $this->logAction($asset, AssetAction::Move, $notes, $locationId);
    }

    /**
     * Check-Out asset to a recipient (Loan).
     */
    public function checkOut(Asset $asset, string $recipientName, string $notes): void
    {
        $asset->shouldLogHistory = false;
        $asset->update(['status' => AssetStatus::Loaned]);

        $this->logAction($asset, AssetAction::CheckOut, $notes, $asset->location_id, $recipientName);
    }

    /**
     * Check-In asset back to storage (Return).
     */
    public function checkIn(Asset $asset, int $locationId, string $notes): void
    {
        $asset->shouldLogHistory = false;
        $asset->update([
            'status' => AssetStatus::InStock,
            'location_id' => $locationId
        ]);

        $this->logAction($asset, AssetAction::CheckIn, $notes, $locationId);
    }

    /**
     * Internal helper to create history record.
     */
    private function logAction(Asset $asset, AssetAction $action, string $notes, ?int $locationId, ?string $recipient = null): void
    {
        AssetHistory::create([
            'asset_id' => $asset->id,
            'user_id' => Auth::id(),
            'status' => $asset->status,
            'location_id' => $locationId,
            'action_type' => $action,
            'recipient_name' => $recipient,
            'notes' => $notes,
        ]);
    }
}
