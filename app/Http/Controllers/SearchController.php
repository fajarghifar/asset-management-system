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
}
