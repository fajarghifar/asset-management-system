<?php

namespace App\Filament\Resources\FixedItemInstances\Pages;

use App\Filament\Resources\FixedItemInstances\FixedItemInstanceResource;
use Filament\Resources\Pages\ManageRecords;

class ManageFixedItemInstances extends ManageRecords
{
    protected static string $resource = FixedItemInstanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    public function getHeading(): string
    {
        return '';
    }
}
