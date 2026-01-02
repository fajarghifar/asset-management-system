<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Asset;
use App\Models\Product;
use App\Models\Location;
use App\Enums\AssetStatus;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class AssetImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        // 1. Resolve Product (By Name or Code)
        $product = Product::where('name', $row['product_name'])
            ->orWhere('code', $row['product_code'] ?? '')
            ->first();

        if (!$product) {
            // Skip or error if product not found?
            // For now, let's assume it fails validation if product_name doesn't exist,
            // but for safety in model() we return null if still invalid.
            return null;
        }

        // 2. Resolve Location
        $location = Location::where('name', $row['location_name'])
            ->when(
                isset($row['site']),
                fn($q) => $q->where('site', $row['site'])
            )
            ->first();

        // 3. Parse Date & Price
        $purchaseDate = isset($row['purchase_date'])
            ? $this->parseDate($row['purchase_date'])
            : null;

        $price = isset($row['purchase_price'])
            ? (int) preg_replace('/[^0-9]/', '', $row['purchase_price'])
            : 0;

        // 4. Resolve Status from String
        $statusStr = strtolower(str_replace(' ', '_', $row['status'] ?? 'in_stock'));
        $status = match ($statusStr) {
            'dipinjam', 'loaned', 'out' => AssetStatus::Loaned,
            'rusak', 'broken', 'maintenance' => AssetStatus::Maintenance,
            'hilang', 'lost' => AssetStatus::Lost,
            'dihapuskan', 'disposed', 'write_off' => AssetStatus::Disposed,
            default => AssetStatus::InStock, // Tersedia
        };

        return new Asset([
            'product_id' => $product->id,
            'location_id' => $location?->id, // validation ensures this is not null usually
            'asset_tag' => $row['asset_tag'] ?? $this->generateAssetTag($product, $location),
            'serial_number' => $row['serial_number'] ?? null,
            'status' => $status,
            'purchase_date' => $purchaseDate,
            'purchase_price' => $price,
            'supplier_name' => $row['supplier'] ?? null,
            'order_number' => $row['order_number'] ?? null,
            'notes' => $row['notes'] ?? 'Imported via Excel',
        ]);
    }

    public function rules(): array
    {
        return [
            'product_name' => 'required_without:product_code|string',
            'product_code' => 'nullable|string',
            'location_name' => 'required|exists:locations,name',
            'asset_tag' => 'nullable|unique:assets,asset_tag',
            'serial_number' => 'nullable|unique:assets,serial_number',
            'status' => 'nullable|string',
            'purchase_date' => 'nullable',
            'purchase_price' => 'nullable',
        ];
    }

    private function parseDate($value)
    {
        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function generateAssetTag($product, $location)
    {
        // Fallback generation if not provided
        // Format: AST-{PROD}-{LOC}-{RAND}
        $prodParams = Str::upper(Str::slug(Str::limit($product->name, 3, ''), ''));
        $locParams = $location ? Str::upper(Str::slug(Str::limit($location->name, 3, ''), '')) : 'GEN';
        return "AST-$prodParams-$locParams-" . rand(1000, 9999);
    }
}
