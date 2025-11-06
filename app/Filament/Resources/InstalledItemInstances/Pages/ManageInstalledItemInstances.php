<?php

namespace App\Filament\Resources\InstalledItemInstances\Pages;

use App\Filament\Resources\InstalledItemInstances\InstalledItemInstanceResource;
use Filament\Resources\Pages\ManageRecords;

class ManageInstalledItemInstances extends ManageRecords
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
