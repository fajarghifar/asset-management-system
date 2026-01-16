<?php

namespace App\Enums;

enum LoanItemType: string
{
    case Asset = 'asset';
    case Consumable = 'consumable';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Asset => 'Asset',
            self::Consumable => 'Consumable',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Asset => 'warning',
            self::Consumable => 'success',
        };
    }
}
