<?php

namespace App\Filament\Resources\Assets\Schemas;

use Filament\Forms\Get;
use App\Models\Location;
use App\Enums\ProductType;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;

class AssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Aset')
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk (Master)')
                            ->relationship('product', 'name', fn ($query) => $query->where('type', ProductType::Asset))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('location_id')
                            ->label('Lokasi Awal')
                            ->relationship('location', 'name')
                            ->getOptionLabelFromRecordUsing(fn(Location $record) => "{$record->name} ({$record->site->value})")
                            ->searchable()
                            ->preload()
                            ->disabledOn('edit')
                            ->required(),
                        TextInput::make('asset_tag')
                            ->label('Tag ID / Kode Aset')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('Ex: LPT-001'),
                        TextInput::make('serial_number')
                            ->label('Serial Number')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('SN Pabrik'),

                        // Detail Pembelian (Gabung sini)
                        DatePicker::make('purchase_date')
                            ->label('Tanggal Beli')
                            ->default(now())
                            ->maxDate(now()),
                        TextInput::make('purchase_price')
                            ->label('Harga Beli')
                            ->integer()
                            ->prefix('Rp')
                            ->maxValue(99999999999),
                        TextInput::make('supplier_name')
                            ->label('Supplier')
                            ->maxLength(100),
                        TextInput::make('order_number')
                            ->label('No. PO / Invoice')
                            ->maxLength(50),

                        // Catatan (Gabung sini)
                        Textarea::make('notes')
                            ->label('Keterangan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
