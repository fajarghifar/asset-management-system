<?php

namespace App\Filament\Resources\InstalledItemInstances\Pages;

use App\Filament\Resources\InstalledItemInstances\InstalledItemInstanceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditInstalledItemInstance extends EditRecord
{
    protected static string $resource = InstalledItemInstanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
