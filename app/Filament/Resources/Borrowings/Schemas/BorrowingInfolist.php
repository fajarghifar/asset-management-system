<?php

namespace App\Filament\Resources\Borrowings\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;

class BorrowingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Peminjaman')
                    ->schema([
                        Grid::make()
                            ->columns(3)
                            ->schema([
                                TextEntry::make('code')
                                    ->label('Kode Peminjaman')
                                    ->weight('bold')
                                    ->copyable()
                                    ->icon('heroicon-o-hashtag')
                                    ->weight('medium')
                                    ->color('primary'),
                                TextEntry::make('borrower_name')
                                    ->label('Peminjam')
                                    ->icon('heroicon-o-user'),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge(),
                                TextEntry::make('borrow_date')
                                    ->label('Tanggal Pinjam')
                                    ->dateTime('d M Y H:i')
                                    ->icon('heroicon-o-calendar-days'),
                                TextEntry::make('expected_return_date')
                                    ->label('Wajib Kembali')
                                    ->dateTime('d M Y H:i')
                                    ->icon('heroicon-o-calendar'),
                                TextEntry::make('actual_return_date')
                                    ->label('Aktual Kembali')
                                    ->dateTime('d M Y H:i')
                                    ->icon('heroicon-o-check-circle')
                                    ->placeholder('-'),
                        ]),
                        ImageEntry::make('proof_image')
                            ->label('Bukti Peminjaman')
                            ->disk('public')
                            ->visible(fn($record) => !empty($record->proof_image))
                            ->columnSpanFull(),
                        TextEntry::make('purpose')
                            ->label('Tujuan Peminjaman')
                            ->columnSpanFull(),
                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->table([
                                TableColumn::make('Barang')->width('30%'),
                                TableColumn::make('Tipe'),
                                TableColumn::make('Jml. Pinjam')->alignRight(),
                                TableColumn::make('Jml. Kembali')->alignRight(),
                            ])
                            ->schema([
                                TextEntry::make('inventoryItem.item.name')
                                    ->weight('medium'),
                                TextEntry::make('inventoryItem.item.type')
                                    ->badge(),
                                TextEntry::make('quantity')
                                    ->suffix(' Unit')
                                    ->alignRight(),
                                TextEntry::make('returned_quantity')
                                    ->suffix(' Unit')
                                    ->alignRight(),
                            ])
                            ->columnSpanFull()
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
