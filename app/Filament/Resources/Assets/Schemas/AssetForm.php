<?php

namespace App\Filament\Resources\Assets\Schemas;

use App\Models\Product;
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
    /**
     * Configure the form schema.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('resources.assets.fields.asset_info'))
                    ->schema([
                        Select::make('product_id')
                            ->label(__('resources.assets.fields.product'))
                            ->relationship(
                                'product',
                                'name',
                                fn($query) => $query->where('type', ProductType::Asset)
                            )
                            ->searchable()
                            ->getOptionLabelFromRecordUsing(fn(Product $record) => $record->full_name)
                            ->preload()
                            ->optionsLimit(50)
                            ->disabledOn('edit')
                            ->required(),

                        Select::make('location_id')
                            ->label(__('resources.assets.fields.location'))
                            ->relationship('location', 'name')
                            ->getOptionLabelFromRecordUsing(fn(Location $record) => $record->full_name)
                            ->searchable(['name', 'site'])
                            ->preload()
                            ->optionsLimit(50)
                            ->disabledOn('edit')
                            ->required(),

                        TextInput::make('asset_tag')
                            ->label(__('resources.assets.fields.asset_tag'))
                            ->disabled()
                            ->dehydrated()
                            ->maxLength(50)
                            ->placeholder('Auto-generated'),

                        TextInput::make('serial_number')
                            ->label(__('resources.assets.fields.serial_number'))
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder(__('resources.assets.fields.serial_number_placeholder')),

                        // Detail Pembelian
                        DatePicker::make('purchase_date')
                            ->label(__('resources.assets.fields.purchase_date'))
                            ->default(now())
                            ->maxDate(now()),

                        TextInput::make('purchase_price')
                            ->label(__('resources.assets.fields.purchase_price'))
                            ->integer()
                            ->default(0)
                            ->prefix('Rp')
                            ->maxValue(99999999999)
                            ->dehydrateStateUsing(fn($state) => $state ?? 0),

                        TextInput::make('supplier_name')
                            ->label(__('resources.assets.fields.supplier'))
                            ->maxLength(100)
                            ->placeholder(__('resources.assets.fields.supplier_placeholder')),

                        TextInput::make('order_number')
                            ->label(__('resources.assets.fields.order_number'))
                            ->maxLength(50),

                        // Catatan
                        Textarea::make('notes')
                            ->label(__('resources.assets.fields.notes'))
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder(__('resources.assets.fields.notes_placeholder')),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
