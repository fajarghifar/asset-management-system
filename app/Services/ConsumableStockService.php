<?php

namespace App\Services;

use App\DTOs\ConsumableStockData;
use App\Models\ConsumableStock;
use App\Exceptions\ConsumableStockException;
use Illuminate\Support\Facades\DB;
use Throwable;

class ConsumableStockService
{
    /**
     * Create a new stock record.
     */
    public function createStock(ConsumableStockData $data): ConsumableStock
    {
        return DB::transaction(function () use ($data) {
            try {
                // Biz Logic: Manual check is still good for UX before DB constraint hit,
                // but let's rely on DB constraint + Exception handling for robustness,
                // or keep it if we want custom message before hitting DB.
                // Keeping manual check for specific 'duplicate' exception flow.
                $exists = ConsumableStock::where('product_id', $data->product_id)
                    ->where('location_id', $data->location_id)
                    ->exists();

                if ($exists) {
                    throw ConsumableStockException::duplicate();
                }

                return ConsumableStock::create($data->toArray());
            } catch (ConsumableStockException $e) {
                throw $e;
            } catch (Throwable $e) {
                throw ConsumableStockException::createFailed($e->getMessage(), $e);
            }
        });
    }

    /**
     * Update an existing stock record.
     */
    public function updateStock(ConsumableStock $stock, ConsumableStockData $data): ConsumableStock
    {
        return DB::transaction(function () use ($stock, $data) {
            try {
                // Check uniqueness only if product or location changed
                if ($data->product_id != $stock->product_id || $data->location_id != $stock->location_id) {
                    $exists = ConsumableStock::where('product_id', $data->product_id)
                        ->where('location_id', $data->location_id)
                        ->where('id', '!=', $stock->id)
                        ->exists();

                    if ($exists) {
                        throw ConsumableStockException::duplicate();
                    }
                }

                $stock->update($data->toArray());

                return $stock->refresh();
            } catch (ConsumableStockException $e) {
                throw $e;
            } catch (Throwable $e) {
                throw ConsumableStockException::updateFailed((string) $stock->id, $e->getMessage(), $e);
            }
        });
    }

    /**
     * Delete a stock record.
     */
    public function deleteStock(ConsumableStock $stock): void
    {
        DB::transaction(function () use ($stock) {
            try {
                if ($stock->quantity > 0) {
                    throw new ConsumableStockException(__("Cannot delete stock with remaining quantity (:quantity). Please adjust quantity to 0 first.", ['quantity' => $stock->quantity]), 422);
                }

                $stock->delete();
            } catch (ConsumableStockException $e) {
                throw $e;
            } catch (Throwable $e) {
                throw ConsumableStockException::deletionFailed((string) $stock->id, $e->getMessage(), $e);
            }
        });
    }
}
