<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Enums\AssetStatus;
use App\Enums\LocationSite;
use App\Services\AssetService;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;

final class AssetsTable extends PowerGridComponent
{
    use WithExport;

    public string $tableName = 'assets-table';
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
            PowerGrid::exportable('assets_export_' . now()->format('Y_m_d'))
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),

            PowerGrid::header()
                ->showSearchInput(),

            PowerGrid::footer()
                ->showPerPage(perPage: 10, perPageValues: [10, 25, 50, 100])
                ->showRecordCount(),
        ];
    }

    public function datasource()
    {
        return Asset::query()
            ->join('products', 'assets.product_id', '=', 'products.id')
            ->join('locations', 'assets.location_id', '=', 'locations.id')
            ->select(
                'assets.*',
                'products.name as product_name',
                'products.code as product_code',
                'locations.name as location_name',
                'locations.code as location_code',
                'locations.site as location_site'
            );
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
            ->add('asset_tag')
            ->add('product_name')
            ->add('location_site', fn($asset) => LocationSite::tryFrom($asset->location_site)?->getLabel())
            ->add('location_name')
            ->add('status_label', function ($asset) {
                $status = $asset->status;
                $color = $status->getColor();
                $icon = $status->getIcon();
                $label = $status->getLabel();

                $colorClasses = match ($color) {
                    'success' => 'bg-green-100 text-green-800 border-green-200',
                    'info' => 'bg-sky-100 text-sky-800 border-sky-200',
                    'primary' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                    'warning' => 'bg-amber-100 text-amber-800 border-amber-200',
                    'danger' => 'bg-red-100 text-red-800 border-red-200',
                    'gray' => 'bg-gray-100 text-gray-800 border-gray-200',
                    default => 'bg-gray-100 text-gray-800',
                };

                return \Illuminate\Support\Facades\Blade::render(
                    '<div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ' . $colorClasses . '">
                        <x-' . $icon . ' class="w-3.5 h-3.5 mr-1" />
                        ' . $label . '
                    </div>'
                );
            })
            // Export Fields
            ->add('product_code_export', fn($asset) => $asset->product_code)
            ->add('product_name_export', fn($asset) => $asset->product_name)
            ->add('location_code_export', fn($asset) => $asset->location_code)
            ->add('location_name_export', fn($asset) => $asset->location_name)
            ->add('location_site_export', fn($asset) => LocationSite::tryFrom($asset->location_site)?->getLabel())
            ->add('serial_number')
            ->add('status_export', fn($asset) => $asset->status->getLabel())
            ->add('purchase_date_export', fn($asset) => $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '-')
            ->add('notes');
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->hidden(),

            Column::make(__('Asset Tag'), 'asset_tag')
                ->sortable()
                ->searchable(),

            Column::make(__('Product'), 'product_name')
                ->sortable()
                ->visibleInExport(false),

            Column::make(__('Site'), 'location_site')
                ->sortable()
                ->visibleInExport(false),

            Column::make(__('Location'), 'location_name')
                ->sortable()
                ->visibleInExport(false),

            Column::make(__('Status'), 'status_label')
                ->sortable()
                ->visibleInExport(false),

            // Export Columns
            Column::make(__('Product Code'), 'product_code_export')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Product Name'), 'product_name_export')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Location Code'), 'location_code_export')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Location Name'), 'location_name_export')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Site'), 'location_site_export')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Serial Number'), 'serial_number')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Status'), 'status_export')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Purchase Date'), 'purchase_date_export')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Notes'), 'notes')
                ->hidden()
                ->visibleInExport(true),

            Column::action(__('Action')),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::multiSelectAsync('product_name', 'product_id')
                ->url(route('ajax.products.assets.search'))
                ->method('POST')
                ->optionValue('value')
                ->optionLabel('text'),

            Filter::multiSelectAsync('location_name', 'location_id')
                ->url(route('ajax.locations.search'))
                ->method('POST')
                ->optionValue('value')
                ->optionLabel('text'),

            Filter::multiSelect('location_site', 'location_site')
                ->dataSource(collect(LocationSite::cases())->map(fn($site) => [
                    'value' => $site->value,
                    'label' => $site->getLabel(),
                ])->toArray())
                ->optionLabel('label')
                ->optionValue('value')
                ->builder(function (Builder $query, array $values) {
                    return $query->whereHas('location', fn($q) => $q->whereIn('site', $values));
                }),

            Filter::multiSelect('status_label', 'status')
                ->dataSource(collect(AssetStatus::cases())->map(fn($status) => [
                    'value' => $status->value,
                    'label' => $status->getLabel(),
                ])->toArray())
                ->optionLabel('label')
                ->optionValue('value'),
        ];
    }

    public function actions(Asset $row): array
    {
        return [
            Button::add('view')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>')
                ->class('bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-md flex items-center justify-center')
                ->route('assets.show', ['asset' => $row->id])
                ->tooltip(__('View Details')),

            Button::add('move')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>')
                ->class('bg-indigo-500 hover:bg-indigo-600 text-white p-2 rounded-md flex items-center justify-center')
                ->dispatch('move-asset', ['assetId' => $row->id])
                ->tooltip(__('Move Asset')),

            Button::add('edit')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>')
                ->class('bg-amber-500 hover:bg-amber-600 text-white p-2 rounded-md flex items-center justify-center')
                ->route('assets.edit', ['asset' => $row->id])
                ->tooltip(__('Edit Asset')),

            Button::add('delete')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>')
                ->class('bg-red-500 hover:bg-red-600 text-white p-2 rounded-md flex items-center justify-center')
                ->dispatch('open-delete-modal', [
                    'component' => 'assets.assets-table',
                    'method' => 'delete-asset',
                    'params' => ['rowId' => $row->id],
                    'title' => __('Delete Asset?'),
                    'description' => __('Are you sure you want to delete this asset? This action cannot be undone.'),
                ])
                ->tooltip(__('Delete Asset')),
        ];
    }

    #[\Livewire\Attributes\On('delete-asset')]
    public function delete($rowId, AssetService $service): void
    {
        $asset = Asset::find($rowId);

        if ($asset) {
            try {
                $service->deleteAsset($asset);
                $this->dispatch('pg:eventRefresh-assets-table');
                $this->dispatch('toast', message: __('Asset deleted successfully.'), type: 'success');
            } catch (\Exception $e) {
                $this->dispatch('toast', message: __('Failed to delete asset: ') . $e->getMessage(), type: 'error');
            }
        }
    }
}
