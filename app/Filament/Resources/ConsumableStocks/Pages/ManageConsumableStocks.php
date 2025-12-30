<?php

namespace App\Filament\Resources\ConsumableStocks\Pages;

use App\Filament\Resources\ConsumableStocks\ConsumableStockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageConsumableStocks extends ManageRecords
{
    protected static string $resource = ConsumableStockResource::class;

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
