<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;

class LoanException extends Exception
{
    public static function createFailed(string $message, ?Throwable $previous = null): self
    {
        Log::error("Loan creation failed: " . $message);
        return new self(__('Failed to create loan: :message', ['message' => $message]), 0, $previous);
    }

    public static function updateFailed(string $message, ?Throwable $previous = null): self
    {
        Log::error("Loan update failed: " . $message);
        return new self(__('Failed to update loan: :message', ['message' => $message]), 0, $previous);
    }

    public static function approveFailed(string $message, ?Throwable $previous = null): self
    {
        Log::error("Loan approval failed: " . $message);
        return new self(__('Failed to approve loan: :message', ['message' => $message]), 0, $previous);
    }

    public static function rejectFailed(string $message, ?Throwable $previous = null): self
    {
        Log::error("Loan rejection failed: " . $message);
        return new self(__('Failed to reject loan: :message', ['message' => $message]), 0, $previous);
    }

    public static function restoreFailed(string $message, ?Throwable $previous = null): self
    {
        Log::error("Loan restoration failed: " . $message);
        return new self(__('Failed to restore loan: :message', ['message' => $message]), 0, $previous);
    }

    public static function deletionFailed(string $message, ?Throwable $previous = null): self
    {
        Log::error("Loan deletion failed: " . $message);
        return new self(__('Failed to delete loan: :message', ['message' => $message]), 0, $previous);
    }

    public static function returnFailed(string $message, ?Throwable $previous = null): self
    {
        Log::error("Loan items return failed: " . $message);
        return new self(__('Failed to return items: :message', ['message' => $message]), 0, $previous);
    }

    public static function insufficientStock(string $productName, int $requested, int $available): self
    {
        return new self(__('Insufficient stock for :product. Requested: :requested, Available: :available', [
            'product' => $productName,
            'requested' => $requested,
            'available' => $available,
        ]));
    }

    public static function assetUnavailable(string $assetTag, string $status): self
    {
        return new self(__('Asset :tag is not available (Status: :status).', [
            'tag' => $assetTag,
            'status' => $status,
        ]));
    }
}
