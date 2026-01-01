<?php

namespace App\Filament\Resources\Loans\Schemas;

use App\Enums\ProductType;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;


class LoanInfolist
{
    /**
     * Configure the info list implementation for viewing Loan details.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Peminjaman')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('code')
                                    ->label('Kode Peminjaman')
                                    ->weight('medium')
                                    ->copyable()
                                    ->color('primary')
                                    ->icon('heroicon-o-hashtag'),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->columnSpan(2),

                                TextEntry::make('loan_date')
                                    ->label('Tanggal Pinjam')
                                    ->dateTime('d M Y H:i')
                                    ->icon('heroicon-o-calendar-days'),

                                TextEntry::make('due_date')
                                    ->label('Tenggat Pengembalian')
                                    ->dateTime('d M Y H:i')
                                    ->icon('heroicon-o-calendar'),

                                TextEntry::make('returned_date')
                                    ->label('Tanggal Kembali')
                                    ->dateTime('d M Y H:i')
                                    ->icon('heroicon-o-check-circle')
                                    ->placeholder('-'),

                                TextEntry::make('user.name')
                                    ->label('PIC (Admin)')
                                    ->icon('heroicon-o-user-circle'),

                                TextEntry::make('borrower_name')
                                    ->label('Peminjam')
                                    ->icon('heroicon-o-user'),
                            ]),

                        ImageEntry::make('proof_image')
                            ->label('Bukti Peminjaman')
                            ->disk('public')
                            ->visible(fn($record) => !empty($record->proof_image))
                            ->columnSpanFull(),

                        TextEntry::make('purpose')
                            ->label('Keperluan')
                            ->columnSpanFull(),

                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        // --- List of Loan Items ---
                        RepeatableEntry::make('loanItems')
                            ->label('Daftar Barang')
                            ->table([
                                TableColumn::make('Barang'),
                                TableColumn::make('Lokasi'),
                                TableColumn::make('Jumlah Pinjam')->alignRight(),
                                TableColumn::make('Jumlah Kembali')->alignRight(),
                            ])
                            ->schema([
                                TextEntry::make('product_name'),

                                // Uses Accessor from LoanItem
                                TextEntry::make('location_name')
                                    ->label('Lokasi')
                                    ->state(fn($record) => $record->location_name),

                                TextEntry::make('quantity_borrowed')
                                    ->alignRight(),

                                TextEntry::make('quantity_returned')
                                    ->alignRight()
                                    ->color(fn($record) => $record->quantity_returned >= $record->quantity_borrowed ? 'success' : 'warning'),
                            ])
                            ->columnSpanFull()
                    ])
                    ->columnSpanFull()
            ]);
    }
}
