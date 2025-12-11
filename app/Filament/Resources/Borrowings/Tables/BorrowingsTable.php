<?php

namespace App\Filament\Resources\Borrowings\Tables;

use App\Models\Borrowing;
use Filament\Tables\Table;
use Filament\Actions\Action;
use App\Enums\BorrowingStatus;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use App\Services\BorrowingApprovalService;

class BorrowingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Daftar Peminjaman')
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('code')
                    ->label('Kode Peminjaman')
                    ->searchable()
                    ->copyable()
                    ->weight('medium')
                    ->color('primary'),
                TextColumn::make('borrower_name')
                    ->label('Peminjam')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('borrow_date')
                    ->label('Tgl. Pinjam')
                    ->date('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('expected_return_date')
                    ->label('Tgl. Tenggat')
                    ->date('d M Y, H:i')
                    ->sortable()
                    ->color(
                        fn(Borrowing $record) =>
                        $record->isOverdue ? 'danger' : 'gray'
                    ),
                TextColumn::make('purpose')
                    ->label('Tujuan')
                    ->limit(30),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()->label('Ajukan Peminjaman'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(BorrowingStatus::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()->visible(fn(Borrowing $record) => $record->status === BorrowingStatus::Pending),
                    Action::make('approve')
                        ->label('Setujui')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Setujui Peminjaman?')
                        ->modalDescription('Stok akan dikurangi dan status barang berubah menjadi dipinjam.')
                        ->visible(fn(Borrowing $record) => $record->status === BorrowingStatus::Pending)
                        ->action(function (Borrowing $record) {
                            try {
                                app(BorrowingApprovalService::class)->approve($record);
                                Notification::make()->success()->title('Peminjaman Disetujui')->send();
                            } catch (\Exception $e) {
                                Notification::make()->danger()->title('Gagal')->body($e->getMessage())->send();
                            }
                        }),
                    Action::make('reject')
                        ->label('Tolak')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Tolak Peminjaman?')
                        ->visible(fn(Borrowing $record) => $record->status === BorrowingStatus::Pending)
                        ->action(function (Borrowing $record) {
                            app(BorrowingApprovalService::class)->reject($record, 'Ditolak oleh Admin via Tabel');
                            Notification::make()->success()->title('Peminjaman Ditolak')->send();
                        }),
                    DeleteAction::make()->visible(fn(Borrowing $record) => $record->status === BorrowingStatus::Pending),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ])
            ])
            ->defaultSort('created_at', 'desc');
    }
}
