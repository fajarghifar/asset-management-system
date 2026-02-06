<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Enums\ProductType;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function search(Request $request)
    {
        $type = $request->query('type');
        $search = $request->query('q');

        return Product::query()
            ->when($type, function ($query, $type) {
                return $query->where('type', $type);
            }, function ($query) {
                // Default to Consumable if no type specified (backward compatibility)
                return $query->where('type', ProductType::Consumable);
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
                ];
            });
    }
}
