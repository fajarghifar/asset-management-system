<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class ProductService
{
    /**
     * Create a new product.
     * Code will be automatically uppercased and name trimmed.
     *
     * @param array $data
     * @return Product
     * @throws \Exception
     */
    public function createProduct(array $data): Product
    {
        // Normalize before transaction
        $data = $this->normalizeData($data);

        return DB::transaction(function () use ($data) {
            try {
                $product = Product::create($data);

                Log::info("Product created", [
                    'sku' => $product->code,
                    'user_id' => Auth::id()
                ]);

                return $product;
            } catch (QueryException $e) {
                // Catch Duplicate Entry specifically (Error 1062)
                if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) {
                    throw ValidationException::withMessages([
                        'code' => "Kode produk '{$data['code']}' sudah terdaftar (Duplikat)."
                    ]);
                }
                throw $e;
            } catch (\Exception $e) {
                Log::error("Failed to create product via Service", ['error' => $e->getMessage()]);
                throw $e;
            }
        });
    }

    /**
     * Update an existing product.
     *
     * @param Product $product
     * @param array $data
     * @return Product
     * @throws \Exception
     */
    public function updateProduct(Product $product, array $data): Product
    {
        $data = $this->normalizeData($data);

        return DB::transaction(function () use ($product, $data) {
            try {
                $product->update($data);

                Log::info("Product updated", ['sku' => $product->code, 'user_id' => Auth::id()]);

                return $product;
            } catch (QueryException $e) {
                if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) {
                    throw ValidationException::withMessages([
                        'code' => "Kode produk '{$data['code']}' sudah digunakan oleh produk lain."
                    ]);
                }
                throw $e;
            }
        });
    }

    /**
     * Delete a product safely.
     *
     * @param Product $product
     * @return bool
     * @throws \Exception
     */
    public function deleteProduct(Product $product): bool
    {
        return DB::transaction(function () use ($product) {
            // Check Dependencies using Exists (Quick Fail)
            if ($product->assets()->exists()) {
                throw ValidationException::withMessages([
                    'product' => "Tidak dapat menghapus produk. Masih ada ASET yang terdaftar."
                ]);
            }

            if ($product->consumableStocks()->where('quantity', '>', 0)->exists()) {
                throw ValidationException::withMessages([
                    'product' => "Tidak dapat menghapus produk. Masih ada STOK CONSUMABLE."
                ]);
            }

            $product->delete();

            Log::info("Product deleted", ['sku' => $product->code, 'user_id' => Auth::id()]);

            return true;
        });
    }

    /**
     * Normalize input data (Uppercase code, etc.)
     */
    protected function normalizeData(array $data): array
    {
        if (isset($data['code'])) {
            $data['code'] = strtoupper(trim($data['code']));
        }
        if (isset($data['name'])) {
            $data['name'] = trim($data['name']);
        }
        return $data;
    }
}
