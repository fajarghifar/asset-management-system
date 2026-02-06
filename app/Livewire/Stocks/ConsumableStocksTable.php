<?php

namespace App\Livewire\Stocks;

use App\Models\Product;
use App\Enums\LocationSite;
use App\Models\ConsumableStock;
use App\Services\ConsumableStockService;
use Illuminate\Database\Eloquent\Builder;
use App\Exceptions\ConsumableStockException;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;

final class ConsumableStocksTable extends PowerGridComponent
{
    use WithExport;

    public string $tableName = 'consumable-stocks-table';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
    }

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::exportable('stocks_export_' . now()->format('Y_m_d'))
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),

            PowerGrid::header()
                ->showSearchInput(),

            PowerGrid::footer()
                ->showPerPage(perPage: 10, perPageValues: [10, 25, 50, 100])
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return ConsumableStock::query()
            ->with(['product', 'location']);
    }

    public function relationSearch(): array
    {
        return [
            'product' => [
                'name',
                'code',
            ],
            'location' => [
                'name',
                'code',
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('product_name', fn($stock) => $stock->product->name)
            ->add('location_site', fn ($stock) => $stock->location->site->getLabel())
            ->add('location_name', fn ($stock) => $stock->location->name)
            ->add('quantity')
            ->add('min_quantity')
            ->add('status_label', function ($stock) {
                if ($stock->quantity <= $stock->min_quantity) {
                    return '<div class="flex items-center justify-center text-red-600"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5"><path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14ZM8 4a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0v-3A.75.75 0 0 1 8 4Zm0 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" /></svg></div>';
                }
                return '<div class="flex items-center justify-center text-green-600"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5"><path fill-rule="evenodd" d="M12.416 3.376a.75.75 0 0 1 .208 1.04l-5 7.5a.75.75 0 0 1-1.154.114l-3-3a.75.75 0 0 1 1.06-1.06l2.353 2.353 4.493-6.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd" /></svg></div>';
            })
            // Export Fields
            ->add('product_code_export', fn($stock) => $stock->product->code)
            ->add('product_name_export', fn($stock) => $stock->product->name)
            ->add('location_code_export', fn($stock) => $stock->location->code)
            ->add('location_name_export', fn($stock) => $stock->location->name)
            ->add('updated_at_formatted', fn ($stock) => $stock->updated_at->format('d/m/Y H:i'));
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->hidden(),

            Column::make(__('Product'), 'product_name', 'product_id')
                ->sortable()
                ->searchable()
                ->visibleInExport(false),



            // Export Columns

            Column::make(__('Product Code'), 'product_code_export')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Product Name'), 'product_name_export')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Site'), 'location_site', 'location_id')
                ->sortable()
                ->visibleInExport(false),

            Column::make(__('Location Code'), 'location_code_export')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Location Name'), 'location_name_export')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Location'), 'location_name', 'location_id')
                ->sortable()
                ->visibleInExport(false),

            Column::make(__('Quantity'), 'quantity')
                ->sortable(),

            Column::make(__('Min Qty'), 'min_quantity')
                ->sortable(),

            Column::make(__('Status'), 'status_label')
                ->visibleInExport(false),

            Column::action(__('Action')),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::multiSelect('product_name', 'product_id')
                ->dataSource(Product::all())
                ->optionLabel('name')
                ->optionValue('id'),

            Filter::select('location_site', 'location_site')
                ->dataSource(collect(LocationSite::cases())->map(fn($site) => [
                    'value' => $site->value,
                    'label' => $site->getLabel(),
                ])->toArray())
                ->optionLabel('label')
                ->optionValue('value')
                ->builder(function (Builder $query, string $value) {
                    return $query->whereHas('location', fn($q) => $q->where('site', $value));
                }),
        ];
    }

    public function actions(ConsumableStock $row): array
    {
        return [
            Button::add('view')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>')
                ->class('bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-md flex items-center justify-center')
                ->dispatch('show-stock', ['stock' => $row->id])
                ->tooltip(__('View Details')),

            Button::add('edit')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>')
                ->class('bg-amber-500 hover:bg-amber-600 text-white p-2 rounded-md flex items-center justify-center')
                ->dispatch('edit-stock', ['stock' => $row->id])
                ->tooltip(__('Edit Stock')),

            Button::add('delete')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>')
                ->class('bg-red-500 hover:bg-red-600 text-white p-2 rounded-md flex items-center justify-center')
                ->dispatch('open-delete-modal', [
                    'component' => 'stocks.consumable-stocks-table',
                    'method' => 'delete-stock',
                    'params' => ['rowId' => $row->id],
                    'title' => __('Delete Stock?'),
                    'description' => __("Are you sure you want to delete this stock? This action cannot be undone."),
                ])
                ->tooltip(__('Delete Stock')),
        ];
    }

    #[\Livewire\Attributes\On('delete-stock')]
    public function delete($rowId, ConsumableStockService $service): void
    {
        $stock = ConsumableStock::find($rowId);

        if ($stock) {
            try {
                $service->deleteStock($stock);
                $this->dispatch('pg:eventRefresh-consumable-stocks-table');
                $this->dispatch('toast', message: __("Stock deleted successfully."), type: 'success');
            } catch (ConsumableStockException $e) {
                $this->dispatch('toast', message: $e->getMessage(), type: 'error');
            } catch (\Exception $e) {
                $this->dispatch('toast', message: __('Failed to delete stock: :message', ['message' => $e->getMessage()]), type: 'error');
            }
        }
    }
}
