<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Category;
use App\Enums\ProductType;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;

final class ProductsTable extends PowerGridComponent
{
    use WithExport;

    public string $tableName = 'products-table';
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
            PowerGrid::exportable('products_export_' . now()->format('Y_m_d'))
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
        return Product::query()->with('category');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('code')
            ->add('category_name', fn ($product) => $product->category->name)
            ->add('type_label', function ($product) {
                $color = match ($product->type->getColor()) {
                    'primary' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300',
                    'warning' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300',
                    default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                };
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $color . '">' . $product->type->getLabel() . '</span>';
            })
            ->add('can_be_loaned_label', function ($product) {
                return $product->can_be_loaned
                    ? '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-green-500 mx-auto"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>'
                    : '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-red-500 mx-auto"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>';
            })
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable()
                ->searchable()
                ->hidden(),

            Column::make('Code', 'code')
                ->sortable()
                ->searchable()
                ->headerAttribute('text-left')
                ->bodyAttribute('text-left text-indigo-600 hover:text-indigo-900'),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable()
                ->headerAttribute('text-left')
                ->bodyAttribute('text-left'),

            Column::make('Category', 'category_name', 'category_id')
                ->sortable()
                ->headerAttribute('text-left')
                ->bodyAttribute('text-left'),

            Column::make('Type', 'type_label', 'type')
                ->headerAttribute('text-left')
                ->bodyAttribute('text-left'),

            Column::make('Loanable', 'can_be_loaned_label')
                ->headerAttribute('text-left')
                ->bodyAttribute('text-left'),

            Column::action('Action')
                ->headerAttribute('text-left')
                ->bodyAttribute('text-left'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::multiSelect('category_name', 'category_id')
                ->dataSource(Category::all())
                ->optionLabel('name')
                ->optionValue('id'),

            Filter::select('type_label', 'type')
                ->dataSource(collect(ProductType::cases())->map(fn($type) => [
                    'value' => $type->value,
                    'label' => $type->getLabel(),
                ])->toArray())
                ->optionLabel('label')
                ->optionValue('value'),

            Filter::boolean('can_be_loaned_label', 'can_be_loaned')
                ->label('Yes', 'No'),
        ];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert('.$rowId.')');
    }

    public function actions(Product $row): array
    {
        return [
            Button::add('view')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>')
                ->class('bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-md flex items-center justify-center dark:bg-blue-700 dark:hover:bg-blue-800')
                ->dispatch('show-product', ['product' => $row->id])
                ->tooltip('View Details'),

            Button::add('edit')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>')
                ->class('bg-amber-500 hover:bg-amber-600 text-white p-2 rounded-md flex items-center justify-center dark:bg-amber-700 dark:hover:bg-amber-800')
                ->dispatch('edit-product', ['product' => $row->id])
                ->tooltip('Edit Product'),

            Button::add('delete')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>')
                ->class('bg-red-500 hover:bg-red-600 text-white p-2 rounded-md flex items-center justify-center dark:bg-red-700 dark:hover:bg-red-800')
                ->dispatch('open-delete-modal', [
                    'component' => 'products.products-table',
                    'method' => 'delete-product',
                    'params' => ['rowId' => $row->id],
                    'title' => 'Delete Product?',
                    'description' => "Are you sure you want to delete product '{$row->name}'? This action cannot be undone.",
                ])
                ->tooltip('Delete Product'),
        ];
    }

    #[\Livewire\Attributes\On('delete-product')]
    public function delete($rowId, ProductService $service): void
    {
        $product = Product::find($rowId);

        if ($product) {
            try {
                $name = $product->name;
                $service->deleteProduct($product);
                $this->dispatch('pg:eventRefresh-products-table');
                $this->dispatch('toast', message: "Product '{$name}' deleted successfully.", type: 'success');
            } catch (\Exception $e) {
                $this->dispatch('toast', message: 'Failed to delete product: ' . $e->getMessage(), type: 'error');
            }
        }
    }
}
