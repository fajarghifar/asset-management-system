<?php

namespace App\Services;

use App\Models\Kit;
use App\DTOs\KitData;
use App\Models\Asset;
use App\Enums\ProductType;
use App\Enums\AssetStatus;
use App\Models\ConsumableStock;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class KitService
{
    /**
     * Create a new Kit with items.
     */
    public function createKit(KitData $data): Kit
    {
        return DB::transaction(function () use ($data) {
            $kit = Kit::create([
                'name' => $data->name,
                'description' => $data->description,
                'is_active' => $data->is_active,
            ]);

            $this->syncItems($kit, $data->items);

            return $kit;
        });
    }

    /**
     * Update an existing Kit.
     */
    public function updateKit(Kit $kit, KitData $data): Kit
    {
        return DB::transaction(function () use ($kit, $data) {
            $kit->update([
                'name' => $data->name,
                'description' => $data->description,
                'is_active' => $data->is_active,
            ]);

            $kit->items()->delete();
            $this->syncItems($kit, $data->items);

            return $kit;
        });
    }

    /**
     * Delete a Kit.
     */
    public function deleteKit(Kit $kit): void
    {
        DB::transaction(function () use ($kit) {
            $kit->items()->delete();
            $kit->delete();
        });
    }

    private function syncItems(Kit $kit, array $items): void
    {
        foreach ($items as $itemData) {
            $kit->items()->create([
                'product_id' => $itemData->product_id,
                'location_id' => $itemData->location_id,
                'quantity' => $itemData->quantity,
                'notes' => $itemData->notes,
            ]);
        }
    }

    /**
     * Resolve Kit Items into concrete Loan Items (Assets/Stocks)
     * based on availability and location priority.
     *
     * @return array Array of items ready for Loan Form
     */
    public function resolveKitToLoanItems(Kit $kit, ?int $preferredLocationId = null): array
    {
        $resolvedItems = [];
        $kit->load('items.product');

        foreach ($kit->items as $item) {
            $product = $item->product;
            $neededQty = $item->quantity;
            $targetLocationId = $item->location_id ?? $preferredLocationId;

            // If no location is specified in Kit OR Priority, we just return the product name
            if (!$targetLocationId) {
                $resolvedItems[] = [
                    'type' => $product->type === ProductType::Asset ? 'Asset' : 'Consumable',
                    'product_name' => $product->name,
                    'item_id' => null,
                    'item_label' => $product->name,
                    'quantity' => $neededQty,
                    'notes' => $item->notes,
                    'is_fallback' => false,
                ];
                continue;
            }

            if ($product->type === ProductType::Asset) {
                // IMPORTANT: User requested strict check. If not found in target location, show name only.
                // We do NOT automatically fallback to other locations here.
                $assets = Asset::where('product_id', $product->id)
                    ->where('status', AssetStatus::InStock)
                    ->where('location_id', $targetLocationId)
                    ->take($neededQty)
                    ->with(['location', 'product'])
                    ->get();

                if ($assets->isEmpty()) {
                    $resolvedItems[] = [
                        'type' => 'Asset',
                        'product_name' => $product->name,
                        'item_id' => null,
                        'item_label' => $product->name, // User requested: "cukup tampilkan nama barang"
                        'quantity' => $neededQty,
                        'notes' => $item->notes,
                        'is_fallback' => true,
                    ];
                }

                foreach ($assets as $asset) {
                    $siteLabel = $asset->location->site instanceof \App\Enums\LocationSite
                        ? $asset->location->site->getLabel()
                        : $asset->location->site;

                    // User requested format: Nama Barang | Tag Asset | Site - Nama Lokasi
                    $resolvedItems[] = [
                        'type' => 'Asset',
                        'product_name' => $product->name,
                        'item_id' => $asset->id,
                        'item_label' => "{$asset->product->name} | {$asset->asset_tag} | {$siteLabel} - {$asset->location->name}",
                        'quantity' => 1,
                        'notes' => $item->notes,
                        'is_fallback' => false,
                    ];
                }
            } elseif ($product->type === ProductType::Consumable) {
                // Strict check in target location
                $stock = ConsumableStock::where('product_id', $product->id)
                    ->where('location_id', $targetLocationId)
                    ->where('quantity', '>=', $neededQty)
                    ->with(['location', 'product'])
                    ->first();

                if ($stock) {
                    $siteLabel = $stock->location->site instanceof \App\Enums\LocationSite
                        ? $stock->location->site->getLabel()
                        : $stock->location->site;

                    // User requested format: Nama Barang | Stock: Quantity | Site - Nama Lokasi
                    $resolvedItems[] = [
                        'type' => 'Consumable',
                        'product_name' => $product->name,
                        'item_id' => $stock->id,
                        'item_label' => "{$product->name} | Stock: {$stock->quantity} | {$siteLabel} - {$stock->location->name}",
                        'quantity' => $neededQty,
                        'notes' => $item->notes,
                        'is_fallback' => false,
                    ];
                } else {
                    $resolvedItems[] = [
                        'type' => 'Consumable',
                        'product_name' => $product->name,
                        'item_id' => null,
                        'item_label' => $product->name, // User requested: "cukup tampilkan nama barang"
                        'quantity' => $neededQty,
                        'notes' => $item->notes,
                        'is_fallback' => true,
                    ];
                }
            }
        }

        return $resolvedItems;
    }

    /**
     * Find best available assets.
     * Priority: Preferred Location -> Other Locations.
     */
    private function findAvailableAssets(int $productId, int $qty, ?int $preferredLocationId): Collection
    {
        $assets = collect();

        if ($preferredLocationId) {
            $assets = Asset::where('product_id', $productId)
                ->where('status', AssetStatus::InStock)
                ->where('location_id', $preferredLocationId)
                ->take($qty)
                ->with(['location', 'product'])
                ->get();
        }

        if ($assets->count() < $qty) {
            $remaining = $qty - $assets->count();

            $fallbackAssets = Asset::where('product_id', $productId)
                ->where('status', AssetStatus::InStock)
                ->where('location_id', '!=', $preferredLocationId)
                ->take($remaining)
                ->with(['location', 'product'])
                ->get();

            $assets = $assets->merge($fallbackAssets);
        }

        return $assets;
    }

    /**
     * Find best available stock.
     * Priority: Preferred Location -> Largest Stock elsewhere.
     */
    private function findAvailableStock(int $productId, int $qty, ?int $preferredLocationId): ?ConsumableStock
    {
        $stock = null;
        if ($preferredLocationId) {
            $stock = ConsumableStock::where('product_id', $productId)
                ->where('location_id', $preferredLocationId)
                ->where('quantity', '>=', $qty)
                ->with(['location', 'product'])
                ->first();
        }

        if ($stock) {
            return $stock;
        }

        return ConsumableStock::where('product_id', $productId)
            ->where('quantity', '>=', $qty)
            ->with(['location', 'product'])
            ->orderByDesc('quantity')
            ->first();
    }
    /**
     * Check availability of all items in a Kit.
     * Returns a summary status and item-level details.
     */
    public function getKitAvailability(Kit $kit, ?int $preferredLocationId = null): array
    {
        $kit->load(['items.product', 'items.location']);
        $details = [];
        $isFullyAvailable = true;

        foreach ($kit->items as $item) {
            $product = $item->product;
            $neededQty = $item->quantity;
            $targetLocationId = $item->location_id ?? $preferredLocationId;

            $availableQty = 0;
            $locationName = 'Any Location';

            if ($targetLocationId) {
                $location = \App\Models\Location::find($targetLocationId);

                if ($location) {
                    $siteLabel = $location->site instanceof \App\Enums\LocationSite
                        ? $location->site->getLabel()
                        : $location->site;
                    $locationName = "{$siteLabel} - {$location->name}";
                } else {
                    $locationName = __('Unknown Location');
                }
            }

            if ($product->type === ProductType::Asset) {
                $query = Asset::where('product_id', $product->id)
                    ->where('status', AssetStatus::InStock);

                if ($targetLocationId) {
                    $query->where('location_id', $targetLocationId);
                }

                $availableQty = $query->count();

            } elseif ($product->type === ProductType::Consumable) {
                $query = ConsumableStock::where('product_id', $product->id);

                if ($targetLocationId) {
                    $query->where('location_id', $targetLocationId);
                }

                $availableQty = $query->sum('quantity');
            }

            $isEnough = $availableQty >= $neededQty;
            if (!$isEnough) $isFullyAvailable = false;

            $details[] = [
                'product_name' => $product->name,
                'location_name' => $locationName,
                'needed_qty' => $neededQty,
                'available_qty' => $availableQty,
                'is_enough' => $isEnough,
                'status' => $isEnough ? __('Available') : __('Insufficient'),
            ];
        }

        return [
            'is_fully_available' => $isFullyAvailable,
            'details' => $details,
        ];
    }
}
