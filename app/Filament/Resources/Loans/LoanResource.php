<?php

namespace App\Filament\Resources\Loans;

use App\Models\Loan;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Loans\Pages\EditLoan;
use App\Filament\Resources\Loans\Pages\ViewLoan;
use App\Filament\Resources\Loans\Pages\ListLoans;
use App\Filament\Resources\Loans\Schemas\LoanForm;
use App\Filament\Resources\Loans\Pages\CreateLoan;
use App\Filament\Resources\Loans\Tables\LoansTable;
use App\Filament\Resources\Loans\Schemas\LoanInfolist;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;
    protected static ?int $navigationSort = 4;

    public static function getModelLabel(): string
    {
        return __('resources.loans.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.loans.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('resources.loans.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('resources.navigation_groups.loans');
    }

    public static function form(Schema $schema): Schema
    {
        return LoanForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LoanInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'user',
                'loanItems' => fn($query) => $query->withProducts(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoans::route('/'),
            'create' => CreateLoan::route('/create'),
            'view' => ViewLoan::route('/{record}'),
            'edit' => EditLoan::route('/{record}/edit'),
        ];
    }
}
