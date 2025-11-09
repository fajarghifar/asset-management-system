<?php

namespace App\Filament\Resources\Areas\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;

class AreaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Area')
                    ->schema([
                        TextEntry::make('code'),
                        TextEntry::make('name'),
                        TextEntry::make('category')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'housing' => 'info',
                                'office' => 'success',
                                'store' => 'warning',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'housing' => 'Perumahan',
                                'office' => 'Kantor',
                                'store' => 'Store',
                                default => $state,
                            }),
                        TextEntry::make('address')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        RepeatableEntry::make('locations')
                            ->hiddenLabel()
                            ->table([
                                TableColumn::make('Kode Lokasi'),
                                TableColumn::make('Nama Lokasi'),
                                TableColumn::make('Deskripsi'),
                            ])
                            ->schema([
                                TextEntry::make('code'),
                                TextEntry::make('name'),
                                TextEntry::make('description')
                            ])
                            ->visible(fn($record) => $record->locations_count > 0)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Lokasi di Area Ini')
                    ->description('Belum ada lokasi yang dibuat untuk area ini.')
                    ->visible(fn($record) => $record->locations_count === 0)
                    ->columnSpanFull(),
            ]);
    }
}
