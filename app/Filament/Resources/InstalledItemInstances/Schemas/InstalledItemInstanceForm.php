<?php

namespace App\Filament\Resources\InstalledItemInstances\Schemas;

use App\Models\Item;
use App\Models\Location;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class InstalledItemInstanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Aset')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode Aset')
                            ->placeholder('Otomatis: [KODE_ITEM]-[TANGGAL]-[ACAK]')
                            ->disabled()
                            ->dehydrated()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->columnSpanFull(),
                        Select::make('item_id')
                            ->label('Nama Barang')
                            ->relationship(
                                name: 'item',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn(Builder $query) => $query
                                    ->where('type', 'installed')
                                    ->orderBy('name')
                            )
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn(Item $record) => "{$record->name} ({$record->code})")
                            ->searchable(['name', 'code'])
                            ->required(),
                        TextInput::make('serial_number')
                            ->label('Nomor Seri')
                            ->maxLength(100)
                            ->unique(ignoreRecord: true)
                            ->nullable(),
                        Select::make('current_location_id')
                            ->label('Lokasi Pemasangan')
                            ->relationship(
                                name: 'currentLocation',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn(Builder $query) => $query->with('area')
                            )
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn(Location $record) => "{$record->name} - {$record->area->name}")
                            ->searchable(['name', 'code'])
                            ->required()
                            ->columnSpanFull(),
                        DatePicker::make('installed_at')
                            ->label('Tanggal Pemasangan')
                            ->required()
                            ->maxDate(now()),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
