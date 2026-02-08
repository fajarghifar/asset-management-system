<?php

namespace App\Http\Controllers\Api;

use App\Enums\AssetStatus;
use App\Enums\LoanItemType;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\ConsumableStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanItemSearchController extends Controller
{
    /**
     * Search for loanable items based on type.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $search = $request->input('q') ?? $request->input('search');
        $type = $request->input('type');

        if (!$type) {
            return response()->json([]);
        }

        $results = [];

        if ($type === LoanItemType::Asset->value) {
            $query = Asset::query()
                ->where('status', AssetStatus::InStock)
                ->with(['product', 'location'])
                ->where(function ($q) use ($search) {
                    $q->where('asset_tag', 'like', "%{$search}%")
                      ->orWhereHas('product', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                })
                ->limit(20);

            $assets = $query->get();

            foreach ($assets as $asset) {
                $productName = $asset->product?->name ?? __('Unknown Product');
                $location = $asset->location ? "{$asset->location->site->getLabel()} - {$asset->location->name}" : __('Unknown Location');

                $results[] = [
                    'id' => $asset->id,
                    'value' => $asset->id,
                    'text' => "{$productName} | {$asset->asset_tag} | {$location}",
                    'type' => 'asset',
                    'quantity_available' => 1,
                ];
            }
        } elseif ($type === LoanItemType::Consumable->value) {
            $query = ConsumableStock::query()
                ->where('quantity', '>', 0)
                ->with(['product', 'location'])
                ->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->limit(20);

            $stocks = $query->get();

            foreach ($stocks as $stock) {
                $productName = $stock->product?->name ?? __('Unknown Product');
                $location = $stock->location ? "{$stock->location->site->getLabel()} - {$stock->location->name}" : __('Unknown Location');

                $results[] = [
                    'id' => $stock->id,
                    'value' => $stock->id,
                    'text' => "{$productName} | Stock: {$stock->quantity} | {$location}",
                    'type' => 'consumable',
                    'quantity_available' => $stock->quantity,
                ];
            }
        }

        return response()->json($results);
    }
}
