<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use App\Enums\ProductType;
use Filament\Tables\Table;
use App\Imports\ProductImport;
use App\Services\ProductService;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use EightyNine\ExcelImport\ExcelImportAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;

class ProductsTable
{
    /**
     * Configure the main table to display products.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->heading(fn() => __('resources.products.plural_label'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('rowIndex')
                    ->label(__('resources.general.fields.row_index'))
                    ->rowIndex(),

                TextColumn::make('code')
                    ->label(__('resources.products.fields.code'))
                    ->searchable()
                    ->copyable()
                    ->weight('medium')
                    ->color('primary'),

                TextColumn::make('name')
                    ->label(__('resources.products.fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label(__('resources.products.fields.category'))
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('type')
                    ->label(__('resources.products.fields.type'))
                    ->sortable()
                    ->badge(),

                // Use the Model accessor `total_stock`
                // This works efficiently because we use `scopeWithStock()` in the Resource
                TextColumn::make('total_stock')
                    ->label(__('resources.products.fields.total_stock'))
                    ->sortable()
                    ->formatStateUsing(function (Product $record, $state) {
                        $unit = $record->type === ProductType::Asset ? 'Unit' : 'Pcs';
                        return "{$state} {$unit}";
                    })
                    ->badge()
                    ->color(fn($state) => (int) $state > 0 ? 'success' : 'danger')
                    ->alignCenter(),

                IconColumn::make('can_be_loaned')
                    ->label(__('resources.products.fields.can_be_loaned'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label(__('resources.general.created_at'))
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->headerActions([
                ExcelImportAction::make()
                    ->label(__('resources.products.actions.import'))
                    ->color('gray')
                    ->icon(icon: 'heroicon-o-arrow-up-tray')
                    ->use(ProductImport::class)
                    ->validateUsing([
                        'nama_produk' => 'required',
                        'kategori' => 'required',
                        'tipe' => 'required',
                    ])
                    ->sampleExcel(
                        sampleData: [
                            [
                                'nama_produk' => 'Laptop Lenovo Thinkpad',
                                'kode_barang' => 'LPT-THINK-01',
                                'kategori' => 'Elektronik',
                                'tipe' => 'asset',
                                'deskripsi' => 'Laptop untuk staff IT',
                            ],
                            [
                                'nama_produk' => 'Kertas A4 Sidu',
                                'kode_barang' => 'ATK-A4-001',
                                'kategori' => 'ATK',
                                'tipe' => 'consumable',
                                'deskripsi' => 'Kertas HVS 70gsm',
                            ],
                        ],
                        fileName: 'template_import_produk.xlsx',
                        sampleButtonLabel: (__('resources.products.actions.download_template')),
                        customiseActionUsing: fn($action) => $action
                            ->color('info')
                            ->icon('heroicon-o-document-arrow-down')
                    ),

                FilamentExportHeaderAction::make('export')
                    ->label(__('resources.products.actions.export'))
                    ->color('gray')
                    ->defaultPageOrientation('landscape')
                    ->disableAdditionalColumns()
                    ->fileName(date('Y-m-d_H-i'))
                    ->defaultFormat('xlsx')
                    ->disableAdditionalColumns(),

                CreateAction::make()->label(__('resources.general.actions.create')),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label(__('resources.products.filters.category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                SelectFilter::make('type')
                    ->label(__('resources.products.filters.type'))
                    ->options(ProductType::class)
                    ->native(false),

                TernaryFilter::make('can_be_loaned')
                    ->label(__('resources.products.filters.loan_status'))
                    ->native(false)
                    ->placeholder(__('resources.products.filters.all'))
                    ->trueLabel(__('resources.products.filters.loanable'))
                    ->falseLabel(__('resources.products.filters.not_loanable')),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->modalHeading(__('resources.products.notifications.delete_title'))
                        ->modalDescription(__('resources.products.notifications.delete_confirm'))
                        ->action(function (Product $record, ProductService $service) {
                            try {
                                $service->deleteProduct($record);
                                Notification::make()
                                    ->success()
                                    ->title(__('resources.products.notifications.delete_success'))
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->danger()
                                    ->title(__('resources.products.notifications.delete_failed'))
                                    ->body($e->getMessage())
                                    ->send();
                            }
                        }),
                ])->dropdownPlacement('left-start'),
            ])
            ->toolbarActions([
                FilamentExportBulkAction::make('export')
                    ->label('Export Data')
                    ->color('gray')
                    ->defaultPageOrientation('landscape')
                    ->disableAdditionalColumns()
                    ->fileName(date('Y-m-d_H-i'))
                    ->defaultFormat('xlsx')
                    ->defaultPageOrientation('landscape')
                    ->disableAdditionalColumns(),
            ]);
    }
}
