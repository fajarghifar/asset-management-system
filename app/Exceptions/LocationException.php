<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class LocationException extends Exception
{
    public static function creationFailed(string $message, ?Throwable $previous = null): self
    {
        // Don't log expected validation/constraint errors as critical system errors
        if ($previous instanceof \Illuminate\Database\UniqueConstraintViolationException) {
            return new self(__("Location code is already registered. Please use another code."), 422, $previous);
        }

        Log::error("Location Creation Failed: $message", ['exception' => $previous]);
        return new self(__("Failed to create location: System error occurred."), 500, $previous);
    }

    public static function updateFailed(string $id, string $message, ?Throwable $previous = null): self
    {
        Log::error("Location Update Failed (ID: $id): $message", ['exception' => $previous]);
        return new self(__("Failed to update location: System error occurred."), 500, $previous);
    }

    public static function deletionFailed(string $id, string $message, ?Throwable $previous = null): self
    {
        // Handle Foreign Key Constraint Violations gracefully
        if ($previous && str_contains($previous->getMessage(), 'Constraint violation')) {
            return new self(__("Failed to delete: This location is being used by other data (Assets/Stocks)."), 409, $previous);
        }

        Log::error("Location Deletion Failed (ID: $id): $message", ['exception' => $previous]);
        return new self(__("Failed to delete location: System error occurred."), 500, $previous);
    }

    public static function inUse(string $message = null): self
    {
        return new self($message ?? __("Location is currently in use and cannot be deleted."), 409);
    }
}
