<?php

namespace App\Filament\Resources\Locations;

use UnitEnum;
use App\Models\Location;
use Filament\Tables\Table;
use App\Enums\LocationSite;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
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
    protected static ?int $navigationSort = 1;

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
                    ->placeholder('Contoh: BT-IT, JMP2-IT'),
                TextInput::make('name')
                    ->label(__('resources.locations.fields.name'))
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Contoh: Ruang Meeting Utama')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label(__('resources.locations.fields.description'))
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading(fn() => __('resources.locations.plural_label'))
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('#')
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
                CreateAction::make()->label(__('resources.general.actions.create') ?? 'Tambah Lokasi'),
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
                    EditAction::make(),
                    DeleteAction::make()
                        ->action(function (Location $record) {
                            try {
                                $record->delete();
                                Notification::make()->success()->title('Lokasi berhasil dihapus')->send();
                            } catch (\Illuminate\Database\QueryException $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Gagal Menghapus')
                                    ->body('Lokasi tidak bisa dihapus karena masih digunakan oleh Aset/Stok.')
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Error Sistem')
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
