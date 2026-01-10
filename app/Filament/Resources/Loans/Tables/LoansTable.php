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
use App\Services\LoanService;
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
            ->heading(fn() => __('resources.loans.plural_label'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('rowIndex')
                    ->label(__('resources.general.fields.row_index'))
                    ->rowIndex(),

                TextColumn::make('code')
                    ->label(__('resources.loans.fields.code'))
                    ->searchable()
                    ->copyable()
                    ->weight('medium')
                    ->color('primary'),

                TextColumn::make('borrower_name')
                    ->label(__('resources.loans.fields.borrower'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('loan_date')
                    ->label(__('resources.loans.fields.loan_date'))
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label(__('resources.loans.fields.due_date'))
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->color(fn(Loan $record) => $record->status === LoanStatus::Overdue ? 'danger' : 'gray'),

                TextColumn::make('returned_date')
                    ->label(__('resources.loans.fields.returned_date'))
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('purpose')
                    ->label(__('resources.loans.fields.purpose'))
                    ->limit(30)
                    ->tooltip(fn(Loan $record) => $record->purpose),

                TextColumn::make('status')
                    ->label(__('resources.loans.fields.status'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('loan_items_count')
                    ->label(__('resources.loans.fields.items_count'))
                    ->counts('loanItems')
                    ->badge()
                    ->color('gray'),
            ])
            ->headerActions([
                CreateAction::make()->label(__('resources.loans.actions.create')),
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
                        ->label(__('resources.loans.actions.approve'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading(__('resources.loans.actions.approve_heading'))
                        ->modalDescription(__('resources.loans.actions.approve_desc'))
                        ->visible(fn($record) => $record->status === LoanStatus::Pending)
                        ->action(function (Loan $record) {
                            try {
                                app(LoanService::class)->approveLoan($record);
                                Notification::make()->success()
                                    ->title(__('resources.loans.notifications.approved_title'))
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()->danger()
                                    ->title(__('resources.loans.notifications.failed_title'))
                                    ->body($e->getMessage())->send();
                            }
                        }),

                    // Reject Action
                    Action::make('reject')
                        ->label(__('resources.loans.actions.reject'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading(__('resources.loans.actions.reject_heading'))
                        ->form([
                            Textarea::make('reason')->required()->label(__('resources.loans.fields.reason'))
                        ])
                        ->visible(fn($record) => $record->status === LoanStatus::Pending)
                        ->action(function (Loan $record, array $data) {
                            app(LoanService::class)->rejectLoan($record, $data['reason']);
                            Notification::make()->success()
                                ->title(__('resources.loans.notifications.rejected_title'))
                                ->send();
                        }),

                    // Return Items Action (Redirect)
                    Action::make('returnItems')
                        ->label(__('resources.loans.actions.return'))
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
