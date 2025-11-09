<?php

namespace App\Filament\Resources\InstalledItemInstances\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\InstalledItemInstances\InstalledItemInstanceResource;

class ViewInstalledItemInstance extends ViewRecord
{
    protected static string $resource = InstalledItemInstanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
