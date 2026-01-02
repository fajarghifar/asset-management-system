<?php

namespace App\Filament\Resources\Products;

use UnitEnum;
use App\Models\Product;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Tables\ProductsTable;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static string|UnitEnum|null $navigationGroup = 'Inventaris';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Data Barang';
    protected static ?string $pluralModelLabel = 'Data Barang';

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    /**
     * Eager load relationships and stock counts to prevent N+1 queries.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['category'])
            ->withStock();
    }
}
