<?php

namespace App\Enums;

enum LoanStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Closed = 'closed';
    case Overdue = 'overdue';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::Approved => __('Approved'),
            self::Rejected => __('Rejected'),
            self::Closed => __('Closed'),
            self::Overdue => __('Overdue'),
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::Closed => 'gray',
            self::Overdue => 'danger',
        };
    }

    public function getBadgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            self::Approved => 'bg-green-100 text-green-800 border-green-200',
            self::Rejected, self::Overdue => 'bg-red-100 text-red-800 border-red-200',
            self::Closed => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }
}
