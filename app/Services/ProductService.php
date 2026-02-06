<?php

namespace App\Services;

use App\DTOs\ProductData;
use App\Models\Product;
use App\Exceptions\ProductException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductService
{
    /**
     * Create a new product.
     */
    public function createProduct(ProductData $data): Product
    {
        return DB::transaction(function () use ($data) {
            try {
                return Product::create($data->toArray());
            } catch (Throwable $e) {
                throw ProductException::createFailed($e->getMessage(), $e);
            }
        });
    }

    /**
     * Update an existing product.
     */
    public function updateProduct(Product $product, ProductData $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            try {
                $product->update($data->toArray());
                return $product->refresh();
            } catch (Throwable $e) {
                throw ProductException::updateFailed((string) $product->id, $e->getMessage(), $e);
            }
        });
    }

    /**
     * Delete a product.
     */
    public function deleteProduct(Product $product): void
    {
        DB::transaction(function () use ($product) {
            try {
                if ($product->assets()->exists()) {
                    throw ProductException::inUse(__("Cannot delete product ':name' because it has associated assets.", ['name' => $product->name]));
                }

                if ($product->consumableStocks()->exists()) {
                    throw ProductException::inUse(__("Cannot delete product ':name' because it has associated consumable stocks.", ['name' => $product->name]));
                }

                $product->delete();
            } catch (ProductException $e) {
                throw $e;
            } catch (Throwable $e) {
                throw ProductException::deletionFailed((string) $product->id, $e->getMessage(), $e);
            }
        });
    }

    /**
     * Get all products, ordered by newest.
     *
     * @return Collection<int, Product>
     */
    public function getAllProducts(): Collection
    {
        return Product::latest()->get();
    }

    /**
     * Generate a new product code.
     * Format: PRD.YYYY.XXXX (e.g., PRD.2025.0001)
     */
    public function generateCode(): string
    {
        $year = date('Y');
        $prefix = "PRD.{$year}.";

        $latestProduct = Product::where('code', 'like', "{$prefix}%")
            ->orderByRaw('LENGTH(code) DESC')
            ->orderBy('code', 'desc')
            ->first();

        if (!$latestProduct) {
            return "{$prefix}001";
        }

        // Extract last number
        $lastCode = $latestProduct->code;
        $lastNumber = (int) str_replace($prefix, '', $lastCode);
        $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        return "{$prefix}{$nextNumber}";
    }
}
