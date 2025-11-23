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
                Section::make('Informasi Instance')
                    ->schema([
                        TextEntry::make('code')
                            ->label('Kode Instance')
                            ->weight('bold')
                            ->copyable()
                            ->badge()
                            ->color('primary')
                            ->columnSpanFull(),
                        TextEntry::make('item.name')
                            ->label('Nama Barang'),
                        TextEntry::make('serial_number')
                            ->label('Nomor Seri')
                            ->placeholder('-')
                            ->fontFamily('mono'),
                        TextEntry::make('currentLocation.name')
                            ->label('Lokasi Saat Ini')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-m-map-pin'),
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
