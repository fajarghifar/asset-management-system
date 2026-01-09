<?php

namespace App\Filament\Resources\Assets\Pages;

use App\Models\Asset;
use App\Models\Location;
use App\Enums\AssetStatus;
use Filament\Actions\Action;
use App\Services\AssetService;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Assets\AssetResource;

class EditAsset extends EditRecord
{
    protected static string $resource = AssetResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $service = app(AssetService::class);
        return $service->updateAsset($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label(__('resources.general.actions.back'))
                ->icon('heroicon-m-arrow-left')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),

            ActionGroup::make([
                // PINDAH LOKASI (Move)
                Action::make('move')
                    ->label(__('resources.assets.actions.move'))
                    ->icon('heroicon-m-arrows-right-left')
                    ->color('gray')
                    ->form([
                        Select::make('location_id')
                            ->label(__('resources.assets.fields.new_location'))
                            ->options(fn() => Location::pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Textarea::make('notes')
                            ->label(__('resources.assets.fields.move_reason'))
                            ->required(),
                    ])
                    ->action(function (Asset $record, array $data, AssetService $service) {
                        $service->move($record, $data['location_id'], $data['notes']);
                        Notification::make()
                            ->success()
                            ->title(__('resources.assets.notifications.move_success'))
                            ->send();
                    }),

                // PEMINJAMAN (Check-Out)
                Action::make('check_out')
                    ->label(__('resources.assets.actions.check_out'))
                    ->icon('heroicon-m-arrow-up-tray')
                    ->color('info')
                    ->visible(fn (Asset $record) => $record->status === AssetStatus::InStock)
                    ->form([
                        TextInput::make('recipient_name')
                            ->label(__('resources.assets.fields.recipient_name'))
                            ->placeholder(__('resources.assets.fields.recipient_placeholder'))
                            ->required()
                            ->maxLength(255),
                        Textarea::make('notes')
                            ->label(__('resources.assets.fields.purpose'))
                            ->required(),
                    ])
                    ->action(function (Asset $record, array $data, AssetService $service) {
                        $service->checkOut($record, $data['recipient_name'], $data['notes']);
                        Notification::make()
                            ->success()
                            ->title(__('resources.assets.notifications.check_out_success', ['name' => $data['recipient_name']]))
                            ->send();
                    }),

                // PENGEMBALIAN (Check-In)
                Action::make('check_in')
                    ->label(__('resources.assets.actions.check_in'))
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('success')
                    ->visible(fn (Asset $record) => $record->status === AssetStatus::Loaned)
                    ->form([
                        Select::make('location_id')
                            ->label(__('resources.assets.fields.return_location'))
                            ->options(fn() => Location::pluck('name', 'id'))
                            ->default(fn(Asset $record) => $record->location_id)
                            ->required(),
                        Textarea::make('notes')
                            ->label(__('resources.assets.fields.return_condition'))
                            ->required(),
                    ])
                    ->action(function (Asset $record, array $data, AssetService $service) {
                        $service->checkIn($record, $data['location_id'], $data['notes']);
                        Notification::make()
                            ->success()
                            ->title(__('resources.assets.notifications.check_in_success'))
                            ->send();
                    }),

                DeleteAction::make()
                    ->modalDescription(__('resources.assets.notifications.delete_confirm'))
                    ->action(function (Asset $record) {
                        try {
                            $record->delete();
                            Notification::make()
                                ->success()
                                ->title(__('resources.assets.notifications.delete_success'))
                                ->send();
                            return redirect($this->getResource()::getUrl('index'));
                        } catch (QueryException $e) {
                            Notification::make()
                                ->danger()
                                ->title(__('resources.assets.notifications.delete_failed'))
                                ->body(__('resources.assets.notifications.delete_failed_body'))
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title(__('resources.assets.notifications.system_error'))
                                ->body($e->getMessage())
                                    ->send();
                        }
                    }),
            ])
            ->button()
            ->hiddenLabel(),
        ];
    }
}
