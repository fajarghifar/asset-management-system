<?php

namespace App\Filament\Resources\Locations;

use BackedEnum;
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
use Illuminate\Validation\ValidationException;
use App\Filament\Resources\Locations\Pages\ManageLocations;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;
    protected static ?string $navigationLabel = 'Lokasi';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('site')
                    ->label('Site / Area')
                    ->options(LocationSite::class)
                    ->required()
                    ->searchable()
                    ->native(false),
                TextInput::make('code')
                    ->label('Kode Lokasi')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Contoh: BT-IT, JMP2-IT, BT-OFF4'),
                TextInput::make('name')
                    ->label('Nama Lokasi')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Contoh: Ruang Meeting Utama')
                    ->columnSpanFull(),
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
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->copyable()
                    ->weight('medium')
                    ->color('primary'),
                TextColumn::make('name')
                    ->label('Nama Lokasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('site')
                    ->label('Site')
                    ->badge()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->wrap()
                    ->tooltip(fn(TextColumn $column) => $column->getState()),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Lokasi'),
            ])
            ->filters([
                SelectFilter::make('site')
                    ->label('Filter Site')
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
}
