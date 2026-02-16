<?php

namespace App\Services;

use Throwable;
use App\Models\Asset;
use App\DTOs\AssetData;
use App\Models\AssetHistory;
use App\Enums\AssetStatus;
use App\Enums\AssetHistoryAction;
use App\Exceptions\AssetException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AssetService
{
    /**
     * Create a new asset and log the initial history.
     */
    public function createAsset(AssetData $data): Asset
    {
        return DB::transaction(function () use ($data) {
            try {

                // Generate Asset Tag if needed
                $assetTag = $data->asset_tag ?? $this->generateAssetTag();

                // Create Asset
                $asset = Asset::create([
                    'product_id' => $data->product_id,
                    'location_id' => $data->location_id,
                    'asset_tag' => $assetTag,
                    'serial_number' => $data->serial_number,
                    'status' => $data->status ?? AssetStatus::InStock,
                    'purchase_date' => $data->purchase_date,
                    'image_path' => $data->image_path,
                    'notes' => $data->notes,
                ]);

                // Log Initial History
                $this->logHistory(
                    asset: $asset,
                    actionType: AssetHistoryAction::Checkin,
                    notes: __('Initial asset registration'),
                    newLocationId: $asset->location_id,
                    newStatus: $asset->status
                );

                return $asset;

            } catch (Throwable $e) {
                throw AssetException::createFailed($e->getMessage(), $e);
            }
        });
    }

    /**
     * Update asset status (e.g. for loans).
     */
    public function updateStatus(Asset $asset, AssetStatus $status, ?string $notes = null): Asset
    {
        return DB::transaction(function () use ($asset, $status, $notes) {
            try {
                $oldStatus = $asset->status;

                if ($oldStatus === $status) {
                    return $asset;
                }

                $asset->update(['status' => $status]);

                $this->logHistory(
                    asset: $asset,
                    actionType: AssetHistoryAction::StatusChange,
                    notes: $notes ?? __('Status changed to :status', ['status' => $status->getLabel()]),
                    newStatus: $status
                );

                return $asset->refresh();

            } catch (Throwable $e) {
                throw AssetException::updateFailed((string) $asset->id, __('Status update failed: ') . $e->getMessage(), $e);
            }
        });
    }

    /**
     * Update an asset and log history if critical fields change.
     */
    public function updateAsset(Asset $asset, AssetData $data): Asset
    {
        return DB::transaction(function () use ($asset, $data) {
            try {
                // Lock for concurrency
                $asset->refresh()->lockForUpdate();

                if ($asset->status === AssetStatus::Loaned && $data->status !== AssetStatus::Loaned) {
                    throw AssetException::updateFailed(
                        (string) $asset->id,
                        __('Status of a loaned asset cannot be changed manually. Please return the asset first.')
                    );
                }

                if ($asset->status !== AssetStatus::Loaned && $data->status === AssetStatus::Loaned) {
                    throw AssetException::updateFailed(
                        (string) $asset->id,
                        __('Status cannot be manually changed to Loaned. Please use the Loans module.')
                    );
                }

                $oldStatus = $asset->status;
                $oldLocationId = $asset->location_id;

                $asset->update($data->toArray());


                // Detect changes
                $newStatus = $asset->status;
                $newLocationId = $asset->location_id;

                $changes = $asset->getChanges();
                unset($changes['updated_at']);

                if (!empty($changes)) {
                    $actionType = AssetHistoryAction::Update;

                    if ($oldStatus !== $newStatus) {
                        $actionType = AssetHistoryAction::StatusChange;
                    } elseif ($oldLocationId !== $newLocationId) {
                        $actionType = AssetHistoryAction::Movement;
                    }

                    $this->logHistory(
                        asset: $asset,
                        actionType: $actionType,
                        notes: $data->history_notes ?? __('Asset updated'),
                        recipientName: $data->recipient_name,
                        newLocationId: $newLocationId,
                        newStatus: $newStatus
                    );
                }

                return $asset->refresh();

            } catch (Throwable $e) {
                throw AssetException::updateFailed((string) $asset->id, $e->getMessage(), $e);
            }
        });
    }

    /**
     * Delete an asset.
     */
    public function deleteAsset(Asset $asset): void
    {
        DB::transaction(function () use ($asset) {
            try {
                $asset->histories()->delete();
                $asset->delete();
            } catch (Throwable $e) {
                throw AssetException::deletionFailed((string) $asset->id, $e->getMessage(), $e);
            }
        });
    }

    /**
     * Helper to log history safely.
     */
    private function logHistory(
        Asset $asset,
        AssetHistoryAction $actionType,
        string $notes,
        ?int $newLocationId = null,
        ?AssetStatus $newStatus = null,
        ?string $recipientName = null
    ): void {
        AssetHistory::create([
            'asset_id' => $asset->id,
            'user_id' => Auth::id(),
            'location_id' => $newLocationId ?? $asset->location_id,
            'status' => $newStatus ?? $asset->status,
            'recipient_name' => $recipientName,
            'action_type' => $actionType->value,
            'notes' => $notes,
        ]);
    }

    /**
     * Generate a unique asset tag.
     */
    public function generateAssetTag(): string
    {
        do {
            $randomCode = strtoupper(\Illuminate\Support\Str::random(4));
            $dateCode = date('ymd');
            $tag = "INV.{$dateCode}.{$randomCode}";
        } while (Asset::where('asset_tag', $tag)->exists());

        return $tag;
    }
}
