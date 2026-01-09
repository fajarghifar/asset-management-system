<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Location;
use App\Models\ConsumableStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ConsumableStockService
{
    /**
     * Create a new stock record.
     *
     * @param array $data
     * @return ConsumableStock
     * @throws \Exception
     */
    public function createStock(array $data): ConsumableStock
    {
        return DB::transaction(function () use ($data) {
            // Check for duplicate stock (Product + Location)
            $exists = ConsumableStock::where('product_id', $data['product_id'])
                ->where('location_id', $data['location_id'])
                ->exists();

            if ($exists) {
                // Fetch names for better error message
                $productName = Product::find($data['product_id'])?->name ?? 'Unknown';
                $locationName = Location::find($data['location_id'])?->name ?? 'Unknown';

                throw ValidationException::withMessages([
                    'location_id' => "Stok untuk produk '{$productName}' di lokasi '{$locationName}' sudah ada. Silakan edit data yang sudah ada.",
                ]);
            }

            try {
                $stock = ConsumableStock::create($data);

                Log::info("Consumable stock created: ID {$stock->id} by User " . Auth::id());

                return $stock;
            } catch (\Exception $e) {
                Log::error("Failed to create consumable stock: " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Update stock record (e.g. min_quantity adjustment).
     *
     * @param ConsumableStock $stock
     * @param array $data
     * @return ConsumableStock
     * @throws \Exception
     */
    public function updateStock(ConsumableStock $stock, array $data): ConsumableStock
    {
        return DB::transaction(function () use ($stock, $data) {
            try {
                $stock->update($data);

                Log::info("Consumable stock updated: ID {$stock->id} by User " . Auth::id());

                return $stock;
            } catch (\Exception $e) {
                Log::error("Failed to update consumable stock ID {$stock->id}: " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Delete stock record.
     *
     * @param ConsumableStock $stock
     * @return bool
     * @throws \Exception
     */
    public function deleteStock(ConsumableStock $stock): bool
    {
        return DB::transaction(function () use ($stock) {
            try {
                $stock->delete();

                Log::info("Consumable stock deleted: ID {$stock->id} by User " . Auth::id());

                return true;
            } catch (\Exception $e) {
                Log::error("Failed to delete consumable stock ID {$stock->id}: " . $e->getMessage());
                throw $e;
            }
        });
    }
}
