<?php

namespace App\Filament\Resources\InstalledItemInstances\Schemas;

use Filament\Schemas\Schema;
use App\Models\InstalledItemInstance;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;

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
                            ->label('Kode Instance'),
                        TextEntry::make('item.name')
                            ->label('Jenis Barang'),
                        TextEntry::make('serial_number')
                            ->label('Nomor Seri')
                            ->placeholder('-'),
                        TextEntry::make('installedLocation.name')
                            ->label('Lokasi Pemasangan Saat Ini'),
                        TextEntry::make('installed_at')
                            ->label('Tanggal Pemasangan Saat Ini')
                            ->date(),
                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Riwayat Lokasi Pemasangan')
                    ->schema([
                        RepeatableEntry::make('locationHistory')
                            ->hiddenLabel()
                            ->table([
                                TableColumn::make('Lokasi'),
                                TableColumn::make('Tanggal Pasang'),
                                TableColumn::make('Tanggal Lepas'),
                                TableColumn::make('Catatan'),
                            ])
                            ->schema([
                                TextEntry::make('location.name')->placeholder('-'),
                                TextEntry::make('installed_at')->date()->placeholder('-'),
                                TextEntry::make('removed_at')->date()->placeholder('Masih Aktif'),
                                TextEntry::make('notes')->placeholder('-'),
                            ])
                            // ->emptyState('Belum ada riwayat lokasi.')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Metadata Sistem')
                    ->schema([
                        TextEntry::make('deleted_at')
                            ->label('Dihapus Pada')
                            ->dateTime()
                            ->visible(fn (InstalledItemInstance $record): bool => $record->trashed()),
                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Diperbarui Pada')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->collapsible()
                    ->columns(2),
            ]);
    }
}
