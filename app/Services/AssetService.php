<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetHistory;
use App\Enums\AssetStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AssetService
{
    /**
     * Create a new asset and log the initial history.
     *
     * @param array $data
     * @return Asset
     * @throws \Exception
     */
    public function createAsset(array $data): Asset
    {
        return DB::transaction(function () use ($data) {
            try {
                // 1. Generate Asset Tag if not present
                $assetTag = $data['asset_tag'] ?? $this->generateAssetTag();

                // 2. Create the Asset
                $asset = Asset::create([
                    'product_id' => $data['product_id'],
                    'location_id' => $data['location_id'],
                    'asset_tag' => $assetTag,
                    'serial_number' => $data['serial_number'] ?? null,
                    'status' => $data['status'] ?? AssetStatus::InStock,
                    'purchase_date' => $data['purchase_date'] ?? null,
                    'image_path' => $data['image_path'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);

                // 2. Log Initial History
                AssetHistory::create([
                    'asset_id' => $asset->id,
                    'user_id' => Auth::id(), // Current logged in user
                    'location_id' => $asset->location_id,
                    'status' => $asset->status,
                    'action_type' => 'checkin', // Initial checkin
                    'notes' => 'Initial asset registration',
                ]);

                return $asset;

            } catch (\Exception $e) {
                Log::error("Failed to create asset: " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Update an asset and log history if critical fields change.
     *
     * @param Asset $asset
     * @param array $data
     * @return Asset
     * @throws \Exception
     */
    public function updateAsset(Asset $asset, array $data): Asset
    {
        return DB::transaction(function () use ($asset, $data) {
            try {
                // Detect changes for history logging
                $oldStatus = $asset->status;
                $oldLocationId = $asset->location_id;

                // Fields to update on the Asset model
                $updateData = [
                    'product_id' => $data['product_id'] ?? $asset->product_id,
                    'asset_tag' => $data['asset_tag'] ?? $asset->asset_tag,
                    'serial_number' => $data['serial_number'] ?? $asset->serial_number,
                    'purchase_date' => $data['purchase_date'] ?? $asset->purchase_date,
                    'image_path' => $data['image_path'] ?? $asset->image_path,
                    'notes' => $data['notes'] ?? $asset->notes,
                ];

                // If location or status is passed, update them
                if (isset($data['location_id'])) {
                    $updateData['location_id'] = $data['location_id'];
                }
                if (isset($data['status'])) {
                    $updateData['status'] = $data['status'];
                }

                $asset->update($updateData);

                // Check if meaningful change occurred (Status or Location)
                $newStatus = $asset->status;
                $newLocationId = $asset->location_id;

                if ($oldStatus !== $newStatus || $oldLocationId !== $newLocationId) {
                    // Determine action type
                    $actionType = 'update';
                    if ($oldStatus !== $newStatus) {
                        $actionType = 'status_change';
                    } elseif ($oldLocationId !== $newLocationId) {
                        $actionType = 'movement';
                    }

                    // Create History Record
                    AssetHistory::create([
                        'asset_id' => $asset->id,
                        'user_id' => Auth::id(),
                        'location_id' => $newLocationId,
                        'status' => $newStatus,
                        'recipient_name' => $data['recipient_name'] ?? null,
                        'action_type' => $actionType,
                        'notes' => $data['history_notes'] ?? 'Asset updated',
                    ]);
                }

                return $asset->fresh();

            } catch (\Exception $e) {
                Log::error("Failed to update asset: " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Delete an asset.
     *
     * @param Asset $asset
     * @return void
     * @throws \Exception
     */
    public function deleteAsset(Asset $asset): void
    {
        DB::transaction(function () use ($asset) {
            try {
                $asset->histories()->delete();
                $asset->delete();
            } catch (\Exception $e) {
                Log::error("Failed to delete asset: " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Generate a unique asset tag.
     * Format: INV.XXXX.YYMMDD
     * Example: INV.XYG3.261101
     */
    private function generateAssetTag(): string
    {
        do {
            $randomCode = strtoupper(\Illuminate\Support\Str::random(4));
            $dateCode = date('ymd');
            $tag = "INV.{$randomCode}.{$dateCode}";
        } while (Asset::where('asset_tag', $tag)->exists());

        return $tag;
    }
}
