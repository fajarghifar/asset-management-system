<?php

namespace App\Filament\Resources\Items\Schemas;

use App\Enums\ItemType;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class ItemInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Barang')
                    ->schema([
                        TextEntry::make('code')
                            ->label('Kode')
                            ->copyable()
                            ->color('primary')
                            ->weight('medium')
                            ->columnSpanFull(),
                        TextEntry::make('name')
                            ->label('Nama Barang'),
                        TextEntry::make('type')
                            ->label('Tipe')
                            ->badge(),
                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
