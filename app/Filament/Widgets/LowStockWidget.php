<?php

namespace App\Filament\Widgets;

use Filament\Tables\Table;
use App\Models\ConsumableStock;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockWidget extends BaseWidget
{
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';

    protected function getHeading(): string
    {
        return __('widgets.tables.low_stock');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ConsumableStock::whereColumn('quantity', '<=', 'min_quantity')
                    ->with(['product', 'location'])
            )
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('product.name')
                    ->label(__('widgets.tables.columns.product'))
                    ->sortable(),

                TextColumn::make('location.site')
                    ->label(__('widgets.tables.columns.site'))
                    ->sortable(),

                TextColumn::make('location.name')
                    ->label(__('widgets.tables.columns.location'))
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label(__('widgets.tables.columns.remaining'))
                    ->color('danger')
                    ->weight('bold'),

                TextColumn::make('min_quantity')
                    ->label(__('widgets.tables.columns.min_limit')),
            ])
            ->defaultSort('quantity', 'asc')
            ->emptyStateHeading(__('widgets.tables.empty.safe_stock'))
            ->emptyStateDescription(__('widgets.tables.empty.safe_stock_desc'));
    }
}
