<?php

namespace App\Filament\Resources\Borrowings;

use App\Models\Borrowing;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Borrowings\Pages\EditBorrowing;
use App\Filament\Resources\Borrowings\Pages\ViewBorrowing;
use App\Filament\Resources\Borrowings\Pages\ListBorrowings;
use App\Filament\Resources\Borrowings\Pages\CreateBorrowing;
use App\Filament\Resources\Borrowings\Schemas\BorrowingForm;
use App\Filament\Resources\Borrowings\Tables\BorrowingsTable;
use App\Filament\Resources\Borrowings\Schemas\BorrowingInfolist;

class BorrowingResource extends Resource
{
    protected static ?string $model = Borrowing::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return BorrowingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BorrowingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BorrowingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBorrowings::route('/'),
            'create' => CreateBorrowing::route('/create'),
            'view' => ViewBorrowing::route('/{record}'),
            'edit' => EditBorrowing::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
