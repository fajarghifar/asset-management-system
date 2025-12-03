<?php

namespace App\Filament\Resources\InstalledItemInstances\RelationManagers;

use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Models\InstalledItemLocationHistory;
use Filament\Resources\RelationManagers\RelationManager;

class LocationHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'locationHistory';

    protected static ?string $title = 'Riwayat Lokasi';

    protected static string|BackedEnum|null $icon = 'heroicon-o-clock';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('location_id')
                    ->label('Lokasi')
                    ->relationship('location', 'name')
                    ->required(),
                DatePicker::make('installed_at')
                    ->label('Tgl. Masuk')
                    ->required(),
                DatePicker::make('removed_at')
                    ->label('Tgl. Keluar'),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['location.area']))
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('location.area.name')
                    ->label('Area')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(
                        fn(InstalledItemLocationHistory $record) => $record->location->area?->category?->getColor() ?? 'gray'
                    ),
                TextColumn::make('location.name')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('installed_at')
                    ->label('Tgl. Masuk')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('removed_at')
                    ->label('Tgl. Keluar')
                    ->date('d M Y')
                    ->placeholder('Masih Aktif')
                    ->sortable(),
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                //
            ])
            ->defaultSort('installed_at', 'desc');
    }
}
