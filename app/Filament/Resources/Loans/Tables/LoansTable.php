<?php

namespace App\Filament\Resources\Loans\Tables;

use App\Models\Loan;
use App\Enums\LoanStatus;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;
use App\Services\LoanApprovalService;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\Loans\Pages\ViewLoan;

class LoansTable
{
    /**
     * Configure the main table to display loans.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Daftar Peminjaman')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->copyable()
                    ->weight('medium')
                    ->color('primary'),

                TextColumn::make('borrower_name')
                    ->label('Peminjam')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('loan_date')
                    ->label('Tanggal Pinjam')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Tenggat Pengembalian')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->color(fn(Loan $record) => $record->status === LoanStatus::Overdue ? 'danger' : 'gray'),

                TextColumn::make('returned_date')
                    ->label('Waktu Kembali')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('purpose')
                    ->label('Keperluan')
                    ->limit(30)
                    ->tooltip(fn(Loan $record) => $record->purpose),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('loan_items_count')
                    ->label('Jumlah Item')
                    ->counts('loanItems')
                    ->badge()
                    ->color('gray'),
            ])
            ->headerActions([
                CreateAction::make()->label('Buat Peminjaman'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(LoanStatus::class)
                    ->native(false),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()->visible(fn($record) => $record->status === LoanStatus::Pending),

                    // Approve Action
                    Action::make('approve')
                        ->label('Setujui Peminjaman')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Setujui Peminjaman?')
                        ->modalDescription('Stok akan dikurangi dan status barang berubah menjadi "Sedang Dipinjam".')
                        ->visible(fn($record) => $record->status === LoanStatus::Pending)
                        ->action(function (Loan $record) {
                            try {
                                app(LoanApprovalService::class)->approve($record);
                                Notification::make()->success()->title('Peminjaman Disetujui')->send();
                            } catch (\Exception $e) {
                                Notification::make()->danger()->title('Gagal')->body($e->getMessage())->send();
                            }
                        }),

                    // Reject Action
                    Action::make('reject')
                        ->label('Tolak')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Tolak Peminjaman?')
                        ->form([
                            Textarea::make('reason')->required()->label('Alasan Penolakan')
                        ])
                        ->visible(fn($record) => $record->status === LoanStatus::Pending)
                        ->action(function (Loan $record, array $data) {
                            app(LoanApprovalService::class)->reject($record, $data['reason']);
                            Notification::make()->success()->title('Peminjaman Ditolak')->send();
                        }),

                    // Return Items Action (Redirect)
                    Action::make('returnItems')
                        ->label('Pengembalian')
                        ->icon('heroicon-o-arrow-left-start-on-rectangle')
                        ->color('info')
                        ->visible(fn($record) => in_array($record->status, [LoanStatus::Approved, LoanStatus::Overdue]))
                        ->url(fn($record) => ViewLoan::getUrl(['record' => $record])),

                    DeleteAction::make()->visible(fn($record) => $record->status === LoanStatus::Pending),
                ])->dropdownPlacement('left-start'),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
