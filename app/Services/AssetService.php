<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Product;
use App\Enums\AssetAction;
use App\Enums\AssetStatus;
use Illuminate\Support\Str;
use App\Models\AssetHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class AssetService
{
    /**
     * Create a new asset and log history.
     *
     * @param array $data
     * @return Asset
     * @throws \Exception
     */
    public function createAsset(array $data): Asset
    {
        return DB::transaction(function () use ($data) {
            $attempts = 0;
            $maxAttempts = 3;

            do {
                try {
                    $attempts++;

                    // Auto-generate Asset Tag if not present
                    if (empty($data['asset_tag'])) {
                        $product = Product::find($data['product_id']);
                        $prefix = $product ? $product->code : 'AST';
                        $data['asset_tag'] = $prefix . '-' . date('Ymd') . '-' . strtoupper(Str::random(4));
                    }

                    // Set default status if not present (DB default is 'in_stock')
                    if (empty($data['status'])) {
                        $data['status'] = AssetStatus::InStock;
                    }

                    $asset = Asset::create($data);

                    // Refetch to ensure everything is consistent (timestamps, etc)
                    // or just rely on $asset if we set status explicitly.
                    // We need status for history logging.

                    // Log Register History
                    $this->logHistory(
                        $asset,
                        AssetAction::Register,
                        'Aset baru berhasil didaftarkan ke dalam sistem.'
                    );

                    Log::info("Asset created: Tag {$asset->asset_tag} by User " . Auth::id());

                    return $asset;
                } catch (QueryException $e) {
                    // Handle Duplicate Entry (Error 1062) for asset_tag collision
                    if ($e->errorInfo[1] === 1062 && str_contains($e->getMessage(), 'asset_tag') && empty($data['asset_tag'])) {
                        if ($attempts >= $maxAttempts) {
                            throw ValidationException::withMessages([
                                'asset_tag' => "Gagal membuat Asset Tag unik setelah {$maxAttempts} percobaan. Silakan coba lagi."
                            ]);
                        }
                        // Retry with new random tag
                        $data['asset_tag'] = null;
                        continue;
                    }
                    throw $e;
                } catch (\Exception $e) {
                    Log::error("Failed to create asset: " . $e->getMessage());
                    throw $e;
                }
            } while ($attempts < $maxAttempts);

            throw new \Exception("Failed to create asset.");
        });
    }

    /**
     * Update an existing asset and log administrative changes.
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
                $asset->fill($data);

                $ignoredColumns = ['updated_at', 'created_at', 'deleted_at', 'status', 'location_id', 'product_id'];
                $dirtyAttributes = array_keys($asset->getDirty());
                $trackedChanges = array_diff($dirtyAttributes, $ignoredColumns);

                $asset->save();

                if (!empty($trackedChanges)) {
                    $changesList = implode(', ', $trackedChanges);
                    $this->logHistory(
                        $asset,
                        AssetAction::Update,
                        "Pembaruan data administratif pada kolom: [{$changesList}]."
                    );
                }

                Log::info("Asset updated: Tag {$asset->asset_tag} by User " . Auth::id());

                return $asset;
            } catch (\Exception $e) {
                Log::error("Failed to update asset ID {$asset->id}: " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Move asset to a new location.
     */
    public function move(Asset $asset, int $locationId, string $notes): void
    {
        DB::transaction(function () use ($asset, $locationId, $notes) {
            $oldLocationName = $asset->location->name ?? 'Unknown';
            $asset->update(['location_id' => $locationId]);

            // Reload to get new location name
            $asset->load('location');
            $newLocationName = $asset->location->name;

            $this->logHistory(
                $asset,
                AssetAction::Move,
                "Aset dipindah dari {$oldLocationName} ke {$newLocationName}. Catatan: {$notes}"
            );
        });
    }

    /**
     * Check-out asset to a recipient.
     */
    public function checkOut(Asset $asset, string $recipientName, string $notes): void
    {
        if ($asset->status !== AssetStatus::InStock) {
            throw ValidationException::withMessages([
                'status' => "Aset ini tidak tersedia untuk dipinjam (Status: {$asset->status->getLabel()})."
            ]);
        }

        DB::transaction(function () use ($asset, $recipientName, $notes) {
            $asset->update(['status' => AssetStatus::Loaned]);

            $this->logHistory(
                $asset,
                AssetAction::CheckOut,
                "Aset dipinjamkan kepada {$recipientName}. Tujuan: {$notes}"
            );
        });
    }

    /**
     * Check-in (return) asset.
     */
    public function checkIn(Asset $asset, int $returnLocationId, string $notes): void
    {
        if ($asset->status !== AssetStatus::Loaned) {
            throw ValidationException::withMessages([
                'status' => "Aset ini sedang tidak dipinjam (Status: {$asset->status->getLabel()})."
            ]);
        }

        DB::transaction(function () use ($asset, $returnLocationId, $notes) {
            $asset->update([
                'status' => AssetStatus::InStock,
                'location_id' => $returnLocationId
            ]);

            $this->logHistory(
                $asset,
                AssetAction::CheckIn,
                "Aset dikembalikan. Kondisi/Catatan: {$notes}"
            );
        });
    }

    /**
     * Delete an asset safely.
     *
     * @param Asset $asset
     * @return bool
     * @throws \Exception
     */
    public function deleteAsset(Asset $asset): bool
    {
        return DB::transaction(function () use ($asset) {
            if ($asset->status === AssetStatus::Loaned) {
                throw ValidationException::withMessages([
                    'product' => "Tidak dapat menghapus aset yang sedang DIPINJAM."
                ]);
            }

            try {
                // Delete histories (assuming no cascade set in DB, safer to do explicit or let DB handle it)
                // If DB has cascade, this is redundant but safe.
                $asset->histories()->delete();

                $asset->delete();

                Log::info("Asset deleted: Tag {$asset->asset_tag} by User " . Auth::id());

                return true;
            } catch (\Exception $e) {
                Log::error("Failed to delete asset ID {$asset->id}: " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Log asset history.
     */
    protected function logHistory(Asset $asset, AssetAction $action, string $note): void
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
