<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ItemType: string implements HasLabel, HasColor
{
    case Fixed = 'fixed';
    case Consumable = 'consumable';
    case Installed = 'installed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Fixed => 'Barang Tetap',
            self::Consumable => 'Barang Habis Pakai',
            self::Installed => 'Barang Terpasang',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Fixed => 'success',
            self::Consumable => 'warning',
            self::Installed => 'info',
        };
    }

    public function getRelationName(): string
    {
        return match ($this) {
            self::Fixed => 'fixedInstances',
            self::Consumable => 'stocks',
            self::Installed => 'installedInstances',
        };
    }
}
