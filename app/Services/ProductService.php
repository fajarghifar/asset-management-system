<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductService
{
    /**
     * Create a new product.
     *
     * @param array<string, mixed> $data
     * @return Product
     * @throws Exception
     */
    public function createProduct(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            try {
                $product = Product::create($data);

                Log::info("Product created successfully: ID {$product->id}");

                return $product;
            } catch (Exception $e) {
                Log::error("Failed to create product: " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Update an existing product.
     *
     * @param Product $product
     * @param array<string, mixed> $data
     * @return Product
     * @throws Exception
     */
    public function updateProduct(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            try {
                $product->update($data);
                $product->refresh();

                Log::info("Product updated successfully: ID {$product->id}");

                return $product;
            } catch (Exception $e) {
                Log::error("Failed to update product ID {$product->id}: " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Delete a product.
     *
     * @param Product $product
     * @return bool
     * @throws Exception
     */
    public function deleteProduct(Product $product): bool
    {
        return DB::transaction(function () use ($product) {
            try {
                // Future: Check dependencies (assets, stocks) before delete?
                // Currently relying on Foreign Key constraints/cascades or UI warnings.

                $product->delete();

                Log::info("Product deleted successfully: ID {$product->id}");

                return true;
            } catch (Exception $e) {
                Log::error("Failed to delete product ID {$product->id}: " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get all products, ordered by newest.
     *
     * @return Collection
     */
    public function getAllProducts(): Collection
    {
        return Product::latest()->get();
    }
}
