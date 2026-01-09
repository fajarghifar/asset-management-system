<?php

namespace App\Filament\Resources\Products\Pages;

use Filament\Actions\Action;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Products\ProductResource;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $service = app(ProductService::class);
        return $service->createProduct($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label(__('resources.general.actions.back'))
                ->icon('heroicon-m-arrow-left')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }
}
