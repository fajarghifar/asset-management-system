<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductType;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class ProductForm
{
    /**
     * Configure the form schema for the Product resource.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Produk')
                    ->description('Masukkan detail informasi dasar barang.')
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode Barang')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->regex('/^[A-Z0-9]+(?:-[A-Z0-9]+)*$/')
                            ->placeholder('Contoh: HDD500, SSD128')
                            ->helperText('Kode ini akan menjadi prefix untuk aset turunan (Ex: LPT01-xxxxx).')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, callable $set) => $set('code', strtoupper($state)))
                            ->disabled(fn($operation) => $operation === 'edit')
                            ->dehydrated(),

                        TextInput::make('name')
                            ->label('Nama Barang')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Nama lengkap barang'),

                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->optionsLimit(20)
                            ->required()
                            ->createOptionForm(null),

                        Select::make('type')
                            ->label('Jenis Barang')
                            ->options(ProductType::class)
                            ->required()
                            ->native(false)
                            ->disabled(fn($operation) => $operation === 'edit')
                            ->dehydrated(),

                        Toggle::make('can_be_loaned')
                            ->label('Bisa Dipinjam')
                            ->onColor('success')
                            ->offColor('danger')
                            ->default(true)
                            ->helperText('Aktifkan untuk barang yang boleh dibawa pulang/dipinjam user.')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Deskripsi tambahan mengenai barang ini...'),
                ])
                ->columns(2)
                ->columnSpanFull(),
            ]);
    }
}
