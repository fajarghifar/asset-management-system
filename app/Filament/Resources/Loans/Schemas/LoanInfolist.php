<?php

namespace App\Filament\Resources\Loans\Schemas;

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
                Section::make(__('resources.loans.fields.loan_info'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('code')
                                    ->label(__('resources.loans.fields.code'))
                                    ->weight('medium')
                                    ->copyable()
                                    ->color('primary')
                                    ->icon('heroicon-o-hashtag'),

                                TextEntry::make('status')
                                    ->label(__('resources.loans.fields.status'))
                                    ->badge()
                                    ->columnSpan(2),

                                TextEntry::make('loan_date')
                                    ->label(__('resources.loans.fields.loan_date'))
                                    ->dateTime('d M Y H:i')
                                    ->icon('heroicon-o-calendar-days'),

                                TextEntry::make('due_date')
                                    ->label(__('resources.loans.fields.due_date'))
                                    ->dateTime('d M Y H:i')
                                    ->icon('heroicon-o-calendar'),

                                TextEntry::make('returned_date')
                                    ->label(__('resources.loans.fields.returned_date'))
                                    ->dateTime('d M Y H:i')
                                    ->icon('heroicon-o-check-circle')
                                    ->placeholder('-'),

                                TextEntry::make('user.name')
                                    ->label(__('resources.loans.fields.pic'))
                                    ->icon('heroicon-o-user-circle'),

                                TextEntry::make('borrower_name')
                                    ->label(__('resources.loans.fields.borrower'))
                                    ->icon('heroicon-o-user'),
                            ]),

                        ImageEntry::make('proof_image')
                            ->label(__('resources.loans.fields.proof_image'))
                            ->disk('public')
                            ->visible(fn($record) => !empty($record->proof_image))
                            ->columnSpanFull(),

                        TextEntry::make('purpose')
                            ->label(__('resources.loans.fields.purpose'))
                            ->columnSpanFull(),

                        TextEntry::make('notes')
                            ->label(__('resources.loans.fields.notes'))
                            ->placeholder('-')
                            ->columnSpanFull(),

                        // --- List of Loan Items ---
                        RepeatableEntry::make('loanItems')
                            ->label(__('resources.loans.fields.items_list'))
                            ->table([
                                TableColumn::make(__('resources.loans.fields.item_name')),
                                TableColumn::make(__('resources.loans.fields.location')),
                                TableColumn::make(__('resources.loans.fields.loan_quantity'))->alignRight(),
                                TableColumn::make(__('resources.loans.fields.return_quantity'))->alignRight(),
                            ])
                            ->schema([
                                TextEntry::make('product_name'),

                                // Uses Accessor from LoanItem
                                TextEntry::make('location_name')
                                    ->label(__('resources.loans.fields.location'))
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
