<?php

namespace App\Filament\Resources\ConsumableStocks;

use App\Models\Product;
use App\Models\Location;
use App\Enums\ProductType;
use Filament\Tables\Table;
use App\Enums\LocationSite;
use Filament\Schemas\Schema;
use App\Models\ConsumableStock;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use App\Imports\ConsumableStockImport;
use Filament\Tables\Columns\TextColumn;
use App\Services\ConsumableStockService;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use EightyNine\ExcelImport\ExcelImportAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Filament\Resources\ConsumableStocks\Pages\ManageConsumableStocks;

class ConsumableStockResource extends Resource
{
    protected static ?string $model = ConsumableStock::class;
    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return __('resources.consumables.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.consumables.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('resources.consumables.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('resources.navigation_groups.inventory');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label(__('resources.consumables.fields.product'))
                    ->relationship('product', 'name', fn($query) => $query->where('type', ProductType::Consumable))
                    ->getOptionLabelFromRecordUsing(callback: fn(Product $record) => $record->full_name)
                    ->searchable()
                    ->preload()
                    ->optionsLimit(20)
                    ->required()
                    ->columnSpanFull(),

                Select::make('location_id')
                    ->label(__('resources.consumables.fields.location'))
                    ->relationship('location', 'name')
                    ->getOptionLabelFromRecordUsing(fn(Location $record) => $record->full_name)
                    ->searchable()
                    ->preload()
                    ->optionsLimit(20)
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('quantity')
                    ->label(__('resources.consumables.fields.current_stock'))
                    ->integer()
                    ->default(0)
                    ->minValue(0)
                    ->required(),

                TextInput::make('min_quantity')
                    ->label(__('resources.consumables.fields.min_stock_alert'))
                    ->integer()
                    ->default(0)
                    ->minValue(0)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading(fn() => __('resources.consumables.plural_label'))
            ->defaultSort('quantity', 'asc')
            ->columns([
                TextColumn::make('rowIndex')
                    ->label(__('resources.general.fields.row_index'))
                    ->rowIndex(),

                TextColumn::make('product.name')
                    ->label(__('resources.consumables.fields.product'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location.site')
                    ->label(__('resources.consumables.fields.site'))
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location.name')
                    ->label(__('resources.consumables.fields.location'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label(__('resources.consumables.fields.remaining_stock'))
                    ->badge()
                    ->color(fn(ConsumableStock $record) => $record->quantity <= $record->min_quantity ? 'danger' : 'success')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('min_quantity')
                    ->label(__('resources.consumables.fields.min_limit'))
                    ->sortable()
                    ->alignCenter(),
            ])
            ->headerActions([
                ExcelImportAction::make()
                    ->label(__('resources.consumables.actions.import'))
                    ->color('gray')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->use(ConsumableStockImport::class)
                    ->validateUsing([
                        'product_name' => 'required',
                        'location_name' => 'required',
                        'quantity' => 'required',
                    ])
                    ->sampleExcel(
                        sampleData: [
                            [
                                'product_name' => 'Kertas HVS A4',
                                'product_code' => 'ATK-001',
                                'location_name' => 'Gudang Utama',
                                'quantity' => 100,
                                'min_stock' => 10,
                            ],
                            [
                                'product_name' => 'Tinta Printer Hitam',
                                'product_code' => '',
                                'location_name' => 'Ruang Staff',
                                'quantity' => 5,
                                'min_stock' => 2,
                            ],
                        ],
                        fileName: 'template_import_stok.xlsx',
                        sampleButtonLabel: (__('resources.consumables.actions.download_template')),
                        customiseActionUsing: fn($action) => $action
                            ->color('info')
                            ->icon('heroicon-o-document-arrow-down')
                    ),

                FilamentExportHeaderAction::make('export')
                    ->label(__('resources.consumables.actions.export'))
                    ->color('gray')
                    ->defaultPageOrientation('landscape')
                    ->fileName('Stok_Consumable_' . date('Y-m-d'))
                    ->defaultFormat('xlsx'),

                CreateAction::make()
                    ->label(__('resources.general.actions.create'))
                    ->using(function (array $data, ConsumableStockService $service): ConsumableStock {
                        return $service->createStock($data);
                    }),
            ])
            ->filters([
                SelectFilter::make('product')
                    ->label(__('resources.consumables.fields.product'))
                    ->relationship('product', 'name', fn ($query) => $query->where('type', ProductType::Consumable))
                    ->searchable()
                    ->preload(),
                Filter::make('filter_location')
                    ->form([
                        Select::make('site')
                            ->label(__('resources.consumables.fields.site'))
                            ->options(LocationSite::class)
                            ->searchable()
                            ->multiple()
                            ->native(false)
                            ->live(),
                        Select::make('location_id')
                            ->label(__('resources.consumables.fields.location'))
                            ->searchable()
                            ->multiple()
                            ->native(false)
                            ->options(fn ($get) =>
                                Location::query()
                                    ->when(
                                        !empty($get('site')),
                                        fn ($q) => $q->whereIn('site', $get('site'))
                                    )
                                    ->pluck('name', 'id')
                            ),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                !empty($data['site']),
                                fn ($q) => $q->whereHas('location', fn ($l) => $l->whereIn('site', $data['site']))
                            )
                            ->when(
                                !empty($data['location_id']),
                                fn ($q) => $q->whereIn('location_id', $data['location_id'])
                            );
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->using(function (ConsumableStock $record, array $data, ConsumableStockService $service): ConsumableStock {
                            return $service->updateStock($record, $data);
                        }),
                    DeleteAction::make()
                        ->modalDescription(__('resources.consumables.notifications.delete_confirm'))
                        ->action(function (ConsumableStock $record, ConsumableStockService $service) {
                            try {
                                $service->deleteStock($record);
                                Notification::make()
                                    ->success()
                                    ->title(__('resources.consumables.notifications.delete_success'))
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->danger()
                                    ->title(__('resources.consumables.notifications.delete_failed'))
                                    ->body($e->getMessage())
                                    ->send();
                            }
                        }),
                ])->dropdownPlacement('left-start'),
            ])
            ->toolbarActions([
                FilamentExportBulkAction::make('export')
                    ->label(__('resources.consumables.actions.export'))
                    ->icon('heroicon-o-arrow-down-tray')
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageConsumableStocks::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['product', 'location']);
    }
}
