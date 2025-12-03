<?php

namespace App\Filament\Resources\InstalledItemInstances\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class InstalledItemInstanceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Aset')
                    ->schema([
                        TextEntry::make('code')
                            ->label('Kode Aset')
                            ->copyable()
                            ->weight('medium')
                            ->color('primary')
                            ->columnSpanFull(),
                        TextEntry::make('item.name')
                            ->label('Nama Barang'),
                        TextEntry::make('serial_number')
                            ->label('Nomor Seri')
                            ->placeholder('-')
                            ->fontFamily('mono'),
                        TextEntry::make('currentLocation')
                            ->label('Lokasi Saat Ini')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-m-map-pin')
                            ->formatStateUsing(
                                fn($record) =>
                                $record->currentLocation
                                ? $record->currentLocation->area->name . ' — ' . $record->currentLocation->name
                                : '-'
                            ),
                        TextEntry::make('installed_at')
                            ->label('Tanggal Pasang')
                            ->date('d F Y'),
                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
