<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class CategoryException extends Exception
{
    public static function createFailed(string $message, ?\Throwable $previous = null): self
    {
        Log::error("Category Creation Failed: $message", ['exception' => $previous]);
        return new self(__("Failed to create category: System error occurred."), 500, $previous);
    }

    public static function updateFailed(string $id, string $message, ?\Throwable $previous = null): self
    {
        Log::error("Category Update Failed (ID: $id): $message", ['exception' => $previous]);
        return new self(__("Failed to update category: System error occurred."), 500, $previous);
    }

    public static function deletionFailed(string $id, string $message, ?\Throwable $previous = null): self
    {
        if ($previous && str_contains($previous->getMessage(), 'Constraint violation')) {
            return new self(__("Failed to delete: This category is being used by other data."), 409, $previous);
        }

        Log::error("Category Deletion Failed (ID: $id): $message", ['exception' => $previous]);
        return new self(__("Failed to delete category: System error occurred."), 500, $previous);
    }

    public static function inUse(?string $message = null): self
    {
        return new self($message ?? __("Category is currently in use and cannot be deleted."), 409);
    }
}
