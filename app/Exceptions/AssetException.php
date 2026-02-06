<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;

class AssetException extends Exception
{
    public static function createFailed(string $message, ?Throwable $previous = null): self
    {
        Log::error("Asset Creation Failed: $message", ['exception' => $previous]);
        return new self(__('Failed to create asset: System error occurred.'), 500, $previous);
    }

    public static function updateFailed(string $id, string $message, ?Throwable $previous = null): self
    {
        Log::error("Asset Update Failed (ID: $id): $message", ['exception' => $previous]);
        return new self(__('Failed to update asset: System error occurred.'), 500, $previous);
    }

    public static function deletionFailed(string $id, string $message, ?Throwable $previous = null): self
    {
        Log::error("Asset Deletion Failed (ID: $id): $message", ['exception' => $previous]);
        return new self(__('Failed to delete asset: System error occurred.'), 500, $previous);
    }

    public static function moveFailed(string $id, string $message, ?Throwable $previous = null): self
    {
        Log::error("Asset Move Failed (ID: $id): $message", ['exception' => $previous]);
        return new self(__('Failed to move asset: System error occurred.'), 500, $previous);
    }
}
