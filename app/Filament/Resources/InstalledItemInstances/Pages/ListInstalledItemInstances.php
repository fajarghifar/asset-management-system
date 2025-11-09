<?php

namespace App\Filament\Resources\InstalledItemInstances\Pages;

use App\Filament\Resources\InstalledItemInstances\InstalledItemInstanceResource;
use Filament\Resources\Pages\ListRecords;

class ListInstalledItemInstances extends ListRecords
{
    protected static string $resource = InstalledItemInstanceResource::class;

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
