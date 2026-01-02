<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     */
    public function model(array $row)
    {
        // 1. Handle Category
        $categoryName = $row['kategori'];
        $categorySlug = Str::slug($categoryName);

        $category = Category::firstOrCreate(
            ['slug' => $categorySlug],
            [
                'name' => $categoryName,
                'description' => 'Imported via Excel'
            ]
        );

        // 2. Handle Type (Asset vs Consumable)
        // Default to Consumable if not specified or invalid
        $typeString = strtolower($row['tipe'] ?? 'consumable');
        $type = match ($typeString) {
            'asset', 'fixed', 'aset', 'barang' => \App\Enums\ProductType::Asset,
            default => \App\Enums\ProductType::Consumable,
        };

        // 3. Create Product
        // We do NOT import stock here because Stock requires Location logic (ConsumableStock)
        // or unique Asset Tags (Asset). This import is for Master Data only.
        return new Product([
            'name' => $row['nama_produk'],
            'code' => $row['kode_barang'] ?? $this->generateCode($row['nama_produk']), // Fallback generation
            'category_id' => $category->id,
            'type' => $type,
            'description' => $row['deskripsi'] ?? null,
            'can_be_loaned' => true, // Default
        ]);
    }

    public function rules(): array
    {
        return [
            'nama_produk' => 'required|string|max:200',
            'kode_barang' => 'nullable|unique:products,code',
            'kategori' => 'required|string',
            'tipe' => 'nullable|string|in:asset,consumable,aset,barang,habis pakai',
        ];
    }

    private function generateCode($name)
    {
        // Simple auto-generation if code is missing in Excel
        return strtoupper(Str::slug(Str::limit($name, 3), '') . '-' . rand(1000, 9999));
    }
}
