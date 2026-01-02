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
                            ->helperText('Kode ini digunakan sebagai awalan untuk aset turunan (Contoh: LPT01-xxxxx).')
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
                            ->label('Kategori Barang')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->optionsLimit(20)
                            ->required()
                            ->createOptionForm(null),

                        Select::make('type')
                            ->label('Tipe Barang')
                            ->options(ProductType::class)
                            ->required()
                            ->native(false)
                            ->disabled(fn($operation) => $operation === 'edit')
                            ->dehydrated(),

                        Toggle::make('can_be_loaned')
                            ->label('Dapat Dipinjam')
                            ->onColor('success')
                            ->offColor('danger')
                            ->default(true)
                            ->helperText('Aktifkan jika barang ini diizinkan untuk dipinjam oleh karyawan.')
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
