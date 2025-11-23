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
                Section::make('Informasi Instance')
                    ->columns(2)
                    ->schema([
                        Select::make('item_id')
                            ->label('Jenis Barang')
                            ->relationship(
                                name: 'item',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn(Builder $query) => $query
                                    ->where('type', 'installed')
                                    ->orderBy('name')
                            )
                            ->getOptionLabelFromRecordUsing(fn(Item $record) => "{$record->name} ({$record->code})")
                            ->searchable(['name', 'code'])
                            ->required(),
                        TextInput::make('code')
                            ->label('Kode Instance')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(30)
                            ->autofocus(),
                        TextInput::make('serial_number')
                            ->label('Nomor Seri')
                            ->maxLength(100)
                            ->unique(ignoreRecord: true)
                            ->nullable(),
                        Select::make('current_location_id')
                            ->label('Lokasi Pemasangan')
                            ->relationship(
                                name: 'currentLocation',
                                titleAttribute: 'name'
                            )
                            ->getOptionLabelFromRecordUsing(fn(Location $record) => "{$record->name} ({$record->code})")
                            ->searchable(['name', 'code'])
                            ->required(),
                        DatePicker::make('installed_at')
                            ->label('Tanggal Pemasangan')
                            ->required(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
