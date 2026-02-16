<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Enums\ProductType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function search(Request $request)
    {
        $type = $request->input('type');
        $search = $request->input('q') ?? $request->input('search') ?? $request->input('term');

        return Product::query()
            ->when($type, function ($query, $type) {
                if ($type !== 'all') {
                    return $query->where('type', $type);
                }
                return $query;
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function ($product) {
                return [
                    'value' => $product->id,
                    'text' => $product->name,
                    'type' => $product->type->value,
                ];
            });
    }

    public function searchAssets(Request $request)
    {
        $request->merge(['type' => ProductType::Asset->value]);
        return $this->search($request);
    }

    public function searchConsumables(Request $request)
    {
        $request->merge(['type' => ProductType::Consumable->value]);
        return $this->search($request);
    }
}
