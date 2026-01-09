<?php

namespace App\Filament\Resources\Assets\Pages;

use Filament\Actions\Action;
use App\Services\AssetService;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Assets\AssetResource;

class CreateAsset extends CreateRecord
{
    protected static string $resource = AssetResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $service = app(AssetService::class);
        return $service->createAsset($data);
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
