<?php

namespace App\Filament\Resources\Areas\Pages;

use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\Areas\AreaResource;

class ManageAreas extends ManageRecords
{
    protected static string $resource = AreaResource::class;

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
