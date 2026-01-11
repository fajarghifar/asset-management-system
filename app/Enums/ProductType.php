<?php

namespace App\Enums;

enum ProductType: string
{
    case Asset = 'asset';
    case Consumable = 'consumable';

    public function getLabel(): string
    {
        return match ($this) {
            self::Asset => 'Asset',
            self::Consumable => 'Consumable',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::Asset => 'primary',
            self::Consumable => 'warning',
        };
    }
}
