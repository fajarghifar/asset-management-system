<?php

namespace App\Filament\Resources\Products\Pages;

use Filament\Actions\Action;
use App\Services\ProductService;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Products\ProductResource;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $service = app(ProductService::class);
        return $service->updateProduct($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label(__('resources.general.actions.back'))
                ->icon('heroicon-m-arrow-left')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),
            DeleteAction::make(),
        ];
    }
}
