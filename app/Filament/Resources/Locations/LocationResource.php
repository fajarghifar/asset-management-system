<?php

namespace App\Filament\Resources\Locations;

use App\Models\Location;
use Filament\Tables\Table;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use App\Filament\Resources\Locations\Pages\ManageLocations;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('area_id')
                    ->label('Area')
                    ->relationship('area', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabledOn('edit'),
                TextInput::make('name')
                    ->label('Nama Lokasi')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Contoh: Ruang Meeting Utama'),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Daftar Lokasi')
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('name')
                    ->label('Nama Lokasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('area.name')
                    ->label('Area')
                    ->sortable()
                    ->badge()
                    ->color(fn(Location $record) => $record->area?->category?->getColor() ?? 'gray'),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->wrap()
                    ->tooltip(fn(TextColumn $column) => $column->getState()),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Lokasi')
                    ->modalDescription('Kode Lokasi akan digenerate otomatis berdasarkan Kode Area.'),
            ])
            ->filters([
                SelectFilter::make('area')
                    ->relationship('area', 'name')
                    ->searchable()
                    ->preload()
                    ->optionsLimit(10),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->iconSize('lg'),
                    EditAction::make()->iconSize('lg'),
                    DeleteAction::make()
                        ->action(function (Location $record) {
                            try {
                                $record->delete();
                                Notification::make()->success()->title('Lokasi berhasil dihapus')->send();
                            } catch (ValidationException $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Gagal Menghapus')
                                    ->body($e->validator->errors()->first())
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('area');
    }
}
