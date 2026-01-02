<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Location;
use App\Enums\ProductType;
use App\Models\ConsumableStock;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ConsumableStockImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     */
    public function model(array $row)
    {
        // 1. Resolve Product (Must be Consumable)
        $product = Product::where('type', ProductType::Consumable)
            ->where(function ($q) use ($row) {
                $q->where('name', $row['product_name'])
                  ->orWhere('code', $row['product_code'] ?? '');
            })
            ->first();

        if (!$product) {
            return null; // Skip if invalid product
        }

        // 2. Resolve Location
        $location = Location::where('name', $row['location_name'])
            ->first();

        if (!$location) {
            return null; // Skip if invalid location
        }

        // 3. Update or Create Stock
        // We look for existing stock record in this location
        return ConsumableStock::updateOrCreate(
            [
                'product_id' => $product->id,
                'location_id' => $location->id,
            ],
            [
                'quantity' => (int) ($row['quantity'] ?? 0),
                'min_quantity' => (int) ($row['min_stock'] ?? 5),
            ]
        );
    }

    public function rules(): array
    {
        return [
            'product_name' => 'required',
            'location_name' => 'required|exists:locations,name',
            'quantity' => 'required|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
        ];
    }
}
