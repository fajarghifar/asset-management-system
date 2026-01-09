<?php

namespace App\Filament\Resources\Locations;

use App\Models\Location;
use Filament\Tables\Table;
use App\Enums\LocationSite;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use App\Services\LocationService;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\Locations\Pages\ManageLocations;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    public static function getModelLabel(): string
    {
        return __('resources.locations.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.locations.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('resources.locations.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('resources.navigation_groups.location');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('site')
                    ->label(__('resources.locations.fields.site'))
                    ->options(LocationSite::class)
                    ->required()
                    ->searchable()
                    ->native(false),
                TextInput::make('code')
                    ->label(__('resources.locations.fields.code'))
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true)
                    ->placeholder(__('resources.locations.fields.code_placeholder')),
                TextInput::make('name')
                    ->label(__('resources.locations.fields.name'))
                    ->required()
                    ->maxLength(100)
                    ->placeholder(__('resources.locations.fields.name_placeholder'))
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label(__('resources.locations.fields.description'))
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading(fn() => __('resources.locations.plural_label'))
            ->columns([
                TextColumn::make('rowIndex')
                    ->label(__('resources.general.fields.row_index'))
                    ->rowIndex(),
                TextColumn::make('code')
                    ->label(__('resources.locations.fields.code'))
                    ->searchable()
                    ->copyable()
                    ->weight('medium')
                    ->color('primary'),
                TextColumn::make('name')
                    ->label(__('resources.locations.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('site')
                    ->label(__('resources.locations.fields.site'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__('resources.locations.fields.description'))
                    ->limit(50)
                    ->wrap()
                    ->tooltip(fn(TextColumn $column) => $column->getState()),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('resources.general.actions.create'))
                    ->using(function (array $data, LocationService $service): Location {
                        return $service->createLocation($data);
                    }),
            ])
            ->filters([
                SelectFilter::make('site')
                    ->label(__('resources.locations.fields.site'))
                    ->options(LocationSite::class)
                    ->native(false)
                    ->searchable()
                    ->multiple(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->using(function (Location $record, array $data, LocationService $service): Location {
                            return $service->updateLocation($record, $data);
                        }),
                    DeleteAction::make()
                        ->action(function (Location $record, LocationService $service) {
                            try {
                                $service->deleteLocation($record);
                                Notification::make()
                                    ->success()
                                    ->title(__('resources.locations.notifications.delete_success'))
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->danger()
                                    ->title(__('resources.locations.notifications.delete_failed'))
                                    ->body($e->getMessage())
                                    ->send();
                            }
                        }),
                ])->dropdownPlacement('left-start'),
            ])
            ->toolbarActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLocations::route('/'),
        ];
    }
}
