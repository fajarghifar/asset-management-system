<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BorrowingStatus: string implements HasLabel, HasColor
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Completed = 'completed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'Menunggu Persetujuan',
            self::Approved => 'Disetujui / Berjalan',
            self::Rejected => 'Ditolak',
            self::Completed => 'Selesai',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Approved => 'info',
            self::Rejected => 'danger',
            self::Completed => 'success',
        };
    }
}
