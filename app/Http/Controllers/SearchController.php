<?php

namespace App\Http\Controllers;

use App\Models\Kit;
use App\Models\Asset;
use App\Models\Product;
use App\Models\Location;
use App\Enums\AssetStatus;
use App\Enums\ProductType;
use App\Enums\LocationSite;
use Illuminate\Http\Request;
use App\Models\ConsumableStock;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    // products logic moved to Api\ProductController
    // public function products(Request $request): JsonResponse { ... }

    public function productLocations(Request $request): JsonResponse
    {
        $productId = $request->query('product_id');
        if (!$productId)
            return response()->json([]);

        $product = Product::find($productId);
        if (!$product)
            return response()->json([]);

        $locations = collect();

        if ($product->type === ProductType::Asset) {
            // Find locations where assets of this product exist (regardless of status)
            $locations = Location::whereHas('assets', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            })->get();
        } else {
            // Find locations where consumable stock record exists (regardless of quantity)
            $locations = Location::whereHas('consumableStocks', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            })->get();
        }

        $results = $locations->map(function ($item) {
            $siteLabel = $item->site instanceof LocationSite
                ? $item->site->getLabel()
                : $item->site;
            return [
                'value' => $item->id,
                'text' => "{$siteLabel} - {$item->name}",
            ];
        });

        return response()->json($results);
    }

    // locations logic moved to Api\LocationController
    // public function locations(Request $request): JsonResponse { ... }

    public function assets(Request $request): JsonResponse
    {
        $search = $request->query('q');

        if (!$search) {
            return response()->json([]);
        }

        try {
            $results = Asset::query()
                ->with(['product', 'location'])
                ->where('status', AssetStatus::InStock)
                ->where(function ($q) use ($search) {
                    $q->where('asset_tag', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($subQ) use ($search) {
                            $subQ->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                })
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'value' => $item->id,
                        'label' => "{$item->asset_tag} - {$item->product->name} (" . ($item->location ? $item->location->full_name : 'No Loc') . ")",
                    ];
                });

            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    public function unified(Request $request): JsonResponse
    {
        $search = $request->query('q');
        $type = $request->query('type'); // 'asset' or 'consumable'

        if (!$search)
            return response()->json([]);

        try {
            $results = collect();

            // Search Assets
            if (!$type || $type === 'asset' || $type === 'undefined') {
                $assets = Asset::query()
                    ->select(
                        'assets.id',
                        'assets.asset_tag',
                        'products.name as product_name',
                        'locations.name as location_name',
                        'locations.site'
                    )
                    ->join('products', 'assets.product_id', '=', 'products.id')
                    ->leftJoin('locations', 'assets.location_id', '=', 'locations.id')
                    ->where('assets.status', AssetStatus::InStock)
                    ->where(function ($q) use ($search) {
                        $q->where('assets.asset_tag', 'like', "%{$search}%")
                            ->orWhere('assets.serial_number', 'like', "%{$search}%")
                            ->orWhere('products.name', 'like', "%{$search}%")
                            ->orWhere('products.code', 'like', "%{$search}%");
                    })
                    ->limit(10)
                    ->get()
                    ->map(function ($item) {
                        $siteLabel = $item->site ? (LocationSite::tryFrom($item->site)?->getLabel() ?? $item->site) : '';
                        $loc = $item->location_name ? "(" . ($siteLabel ? "{$siteLabel} - " : "") . "{$item->location_name})" : '';

                        return [
                            'value' => 'asset_' . $item->id,
                            'id' => $item->id,
                            'type' => 'asset',
                            'label' => "{$item->product_name} ({$item->asset_tag}) {$loc}",
                            'description' => $item->location_name ?? '-'
                        ];
                    });
                $results = $results->merge($assets);
            }

            // Search Consumables
            if (!$type || $type === 'consumable' || $type === 'undefined') {
                $stocks = ConsumableStock::query()
                    ->select(
                        'consumable_stocks.id',
                        'consumable_stocks.quantity',
                        'products.name as product_name',
                        'locations.name as location_name',
                        'locations.site'
                    )
                    ->join('products', 'consumable_stocks.product_id', '=', 'products.id')
                    ->leftJoin('locations', 'consumable_stocks.location_id', '=', 'locations.id')
                    ->where('consumable_stocks.quantity', '>', 0)
                    ->where(function ($q) use ($search) {
                        $q->where('products.name', 'like', "%{$search}%")
                            ->orWhere('products.code', 'like', "%{$search}%");
                    })
                    ->limit(10)
                    ->get()
                    ->map(function ($item) {
                        $siteLabel = $item->site ? (LocationSite::tryFrom($item->site)?->getLabel() ?? $item->site) : '';
                        $loc = $item->location_name ? "(" . ($siteLabel ? "{$siteLabel} - " : "") . "{$item->location_name})" : '';

                        return [
                            'value' => 'stock_' . $item->id,
                            'id' => $item->id,
                            'type' => 'consumable',
                            'label' => "{$item->product_name} (Stock: {$item->quantity}) {$loc}",
                            'description' => "Qty: {$item->quantity}"
                        ];
                    });
                $results = $results->merge($stocks);
            }

            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    public function kits(Request $request): JsonResponse
    {
        $search = $request->query('q');

        if (!$search) {
            return response()->json([]);
        }

        $results = Kit::query()
            ->where('is_active', true)
            ->where('name', 'like', "%{$search}%")
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id,
                    'label' => $item->name,
                ];
            });

        return response()->json($results);
    }
}
