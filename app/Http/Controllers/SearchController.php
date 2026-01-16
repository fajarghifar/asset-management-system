<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    public function products(Request $request): JsonResponse
    {
        $search = $request->query('q');

        if (!$search) {
            return response()->json([]);
        }

        $results = Product::query()
            ->where('name', 'like', "%{$search}%")
            ->orWhere('code', 'like', "%{$search}%")
            ->take(10)
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id,
                    'label' => "{$item->name} ({$item->code})",
                ];
            });

        return response()->json($results);
    }

    public function locations(Request $request): JsonResponse
    {
        $search = $request->query('q');

        if (!$search) {
            return response()->json([]);
        }

        $results = Location::query()
            ->where('name', 'like', "%{$search}%")
            ->orWhere('site', 'like', "%{$search}%")
            ->take(10)
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id,
                    'label' => $item->full_name,
                ];
            });

        return response()->json($results);
    }

    public function assets(Request $request): JsonResponse
    {
        $search = $request->query('q');
        \Illuminate\Support\Facades\Log::info("Searching assets: " . $search);

        if (!$search) {
            return response()->json([]);
        }

        try {
            $results = \App\Models\Asset::query()
                ->with(['product', 'location'])
                ->where('status', \App\Enums\AssetStatus::InStock)
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

            \Illuminate\Support\Facades\Log::info("Found " . $results->count() . " assets.");
            return response()->json($results);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Asset Search Error: " . $e->getMessage());
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
                $assets = \App\Models\Asset::query()
                    ->select(
                        'assets.id',
                        'assets.asset_tag',
                        'products.name as product_name',
                        'locations.name as location_name',
                        'locations.site'
                    )
                    ->join('products', 'assets.product_id', '=', 'products.id')
                    ->leftJoin('locations', 'assets.location_id', '=', 'locations.id')
                    ->where('assets.status', \App\Enums\AssetStatus::InStock)
                    ->where(function ($q) use ($search) {
                        $q->where('assets.asset_tag', 'like', "%{$search}%")
                            ->orWhere('assets.serial_number', 'like', "%{$search}%")
                            ->orWhere('products.name', 'like', "%{$search}%")
                            ->orWhere('products.code', 'like', "%{$search}%");
                    })
                    ->limit(10)
                    ->get()
                    ->map(function ($item) {
                        $siteLabel = $item->site ? (\App\Enums\LocationSite::tryFrom($item->site)?->getLabel() ?? $item->site) : '';
                        $loc = $item->location_name ? "({$item->location_name}" . ($siteLabel ? " - {$siteLabel}" : "") . ")" : '';

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
                $stocks = \App\Models\ConsumableStock::query()
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
                        $siteLabel = $item->site ? (\App\Enums\LocationSite::tryFrom($item->site)?->getLabel() ?? $item->site) : '';
                        $loc = $item->location_name ? "({$item->location_name}" . ($siteLabel ? " - {$siteLabel}" : "") . ")" : '';

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
            \Illuminate\Support\Facades\Log::error("Unified Search Error: " . $e->getMessage());
            return response()->json([], 500);
        }
    }
}
