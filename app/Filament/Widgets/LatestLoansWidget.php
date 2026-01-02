<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestLoansWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        return __('widgets.tables.latest_loans');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Loan::query()->latest()->limit(5)
            )
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('borrower_name')
                    ->label(__('widgets.tables.columns.borrower'))
                    ->limit(20),

                TextColumn::make('loan_date')
                    ->label(__('widgets.tables.columns.loan_date'))
                    ->date('d M Y'),

                TextColumn::make('due_date')
                    ->label(__('widgets.tables.columns.due_date'))
                    ->date('d M Y')
                    ->color('warning'),

                TextColumn::make('returned_date')
                    ->label(__('widgets.tables.columns.returned_date'))
                    ->date('d M Y')
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->label(__('widgets.tables.columns.status'))
                    ->badge(),
            ])
            ->actions([
                Action::make('view')
                    ->label(__('filament-actions::view.single.label')) // Use default filament trans or custom? Custom for consistency.
                    ->url(fn (Loan $record): string => route('filament.admin.resources.loans.view', $record))
                    ->icon('heroicon-m-eye')
                    ->size('xs'),
            ]);
    }
}
