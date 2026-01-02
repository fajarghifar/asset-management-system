<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AssetStatus: string implements HasLabel, HasColor, HasIcon
{
    case InStock = 'in_stock';
    case Loaned = 'loaned';
    case Installed = 'installed';
    case Maintenance = 'maintenance';
    case Broken = 'broken';
    case Lost = 'lost';
    case Disposed = 'disposed';

    public function getLabel(): ?string
    {
        return __('enums.asset_status.' . $this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::InStock => 'success',
            self::Installed => 'info',
            self::Loaned => 'primary',
            self::Maintenance => 'warning',
            self::Broken => 'danger',
            self::Lost => 'danger',
            self::Disposed => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::InStock => 'heroicon-m-check-circle',
            self::Loaned => 'heroicon-m-user',
            self::Installed => 'heroicon-m-server',
            self::Maintenance => 'heroicon-m-wrench-screwdriver',
            self::Broken => 'heroicon-m-x-circle',
            self::Lost => 'heroicon-m-question-mark-circle',
            self::Disposed => 'heroicon-m-trash',
        };
    }
}
