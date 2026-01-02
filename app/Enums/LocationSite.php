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
        return __('enums.location_site.' . $this->value);
    }
}
