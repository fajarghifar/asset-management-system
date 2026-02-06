<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class ConsumableStockException extends Exception
{
    public static function createFailed(string $message, ?Throwable $previous = null): self
    {
        if ($previous instanceof \Illuminate\Database\UniqueConstraintViolationException) {
            return new self(__("Stock for this product at this location already exists."), 422, $previous);
        }

        Log::error("Stock Creation Failed: $message", ['exception' => $previous]);
        return new self(__("Failed to create stock record: System error occurred."), 500, $previous);
    }

    public static function updateFailed(string $id, string $message, ?Throwable $previous = null): self
    {
        if ($previous instanceof \Illuminate\Database\UniqueConstraintViolationException) {
            return new self(__("Stock for this product at this location already exists."), 422, $previous);
        }

        Log::error("Stock Update Failed (ID: $id): $message", ['exception' => $previous]);
        return new self(__("Failed to update stock record: System error occurred."), 500, $previous);
    }

    public static function deletionFailed(string $id, string $message, ?Throwable $previous = null): self
    {
        Log::error("Stock Deletion Failed (ID: $id): $message", ['exception' => $previous]);
        return new self(__("Failed to delete stock record: System error occurred."), 500, $previous);
    }

    public static function duplicate(): self
    {
        return new self(__("Stock record for this product and location already exists."), 422);
    }
}
