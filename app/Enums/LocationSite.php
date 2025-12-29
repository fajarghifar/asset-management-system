<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LocationSite: string implements HasLabel
{
    case BT = 'BT';
    case JMP1 = 'JMP1';
    case JMP2 = 'JMP2';
    case TGS = 'TGS';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BT => 'BT Batik Trusmi',
            self::JMP1 => 'JMP 1',
            self::JMP2 => 'JMP 2',
            self::TGS => 'TGS',
        };
    }
}
