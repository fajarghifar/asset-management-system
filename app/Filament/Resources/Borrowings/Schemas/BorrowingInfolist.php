<?php

namespace App\Filament\Resources\Borrowings\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
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
                                    ->icon('heroicon-o-hashtag'),
                            TextEntry::make('user.name')
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
                        TextEntry::make('purpose')
                            ->label('Tujuan Peminjaman')
                            ->columnSpanFull(),
                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->table(
                                [
                                    TableColumn::make('Barang')->width('30%'),
                                    TableColumn::make('Tipe'),
                                    TableColumn::make('Jml. Pinjam')->alignRight(),
                                    TableColumn::make('Jml. Kembali')->alignRight(),
                                ]
                            )
                            ->schema([
                                TextEntry::make('item.name')
                                    ->weight('medium'),

                                TextEntry::make('item.type')
                                    ->label('Tipe')
                                    ->badge(),

                                TextEntry::make('quantity')
                                    ->label('Jml. Pinjam')
                                    ->suffix(' Unit')
                                    ->alignRight(),

                                TextEntry::make('returned_quantity')
                                    ->label('Jml. Kembali')
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
