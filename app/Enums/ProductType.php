<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProductType: string implements HasLabel, HasColor
{
    case Asset = 'asset';
    case Consumable = 'consumable';

    public function getLabel(): ?string
    {
        return __('enums.product_type.' . $this->value);
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::Asset => 'primary',
            self::Consumable => 'warning',
        };
    }
}
