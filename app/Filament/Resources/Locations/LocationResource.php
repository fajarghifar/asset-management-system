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
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;
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
                    ->relationship('area','name')
                    ->searchable()
                    ->preload()
                    ->optionsLimit(10)
                    ->required(),
                TextInput::make('code')
                    ->label('Kode Lokasi')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(30)
                    ->helperText('Contoh: JMP1-RM1, BT-EVT')
                    ->autofocus(),
                TextInput::make('name')
                    ->label('Nama Lokasi')
                    ->required()
                    ->maxLength(100),
                Toggle::make('is_borrowable')
                    ->label('Dapat Dipinjam?')
                    ->default(false)
                    ->onColor('success')
                    ->offColor('gray')
                    ->inline(false),
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
                    ->rowIndex()
                    ->width('50px'),
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->label('Nama Lokasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('area.name')
                    ->label('Area')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn(Location $record) => match ($record->area?->category) {
                        'housing' => 'info',
                        'office' => 'success',
                        'store' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(40),
                IconColumn::make('is_borrowable')
                    ->label('Dapat Dipinjam?')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->tooltip(fn(Location $record) => $record->is_borrowable ? 'Ya' : 'Tidak'),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Lokasi'),
            ])
            ->filters([
                SelectFilter::make('area')
                    ->relationship('area', 'name')
                    ->multiple(),

                TernaryFilter::make('is_borrowable')
                    ->label('Dapat Dipinjam?')
                    ->trueLabel('Hanya yang bisa dipinjam')
                    ->falseLabel('Hanya yang tidak bisa dipinjam'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->iconSize('lg'),
                    EditAction::make()->iconSize('lg'),
                    // DeleteAction::make()->iconSize('lg')
                    //     ->requiresConfirmation()
                    //     ->modalHeading('Hapus Lokasi?')
                    //     ->modalDescription('Lokasi yang tidak digunakan dalam peminjaman bisa dihapus. Pastikan tidak ada data terkait.')
                    //     ->visible(fn(Location $record) => $record->roomBookings()->count() === 0),
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
