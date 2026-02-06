<?php

namespace App\Livewire\Assets;

use App\Models\AssetHistory;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class AssetHistoriesTable extends PowerGridComponent
{
    public string $tableName = 'asset-histories-table';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public $assetId;

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput(),

            PowerGrid::footer()
                ->showPerPage(perPage: 10, perPageValues: [10, 25, 50])
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return AssetHistory::query()
            ->where('asset_id', $this->assetId)
            ->with(['user', 'location']);
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('created_at_formatted', fn ($history) => $history->created_at->format('d M Y H:i'))
            ->add('action_type_label', fn($history) => __('history.action.' . $history->action_type))
            ->add('recipient_name', fn ($history) => $history->recipient_name ?? '-')
            ->add('location_name', fn ($history) => $history->location?->full_name ?? '-')
            ->add('user_name', fn($history) => $history->user?->name ?? __('System'))
            ->add('notes_truncated', fn ($history) => str($history->notes)->limit(30))
            ->add('status_label', function ($history) {
                if (!$history->status) return '-';
                $status = $history->status;
                $color = $status->getColor(); // Using Enum method
                $label = $status->getLabel();

                $colorClasses = match ($color) {
                    'success' => 'bg-green-100 text-green-800',
                    'danger' => 'bg-red-100 text-red-800',
                    'warning' => 'bg-yellow-100 text-yellow-800',
                    'info' => 'bg-blue-100 text-blue-800',
                    'gray' => 'bg-gray-100 text-gray-800',
                    default => 'bg-indigo-100 text-indigo-800',
                };

                return sprintf(
                    '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium %s">%s</span>',
                    $colorClasses,
                    $label
                );
            });
    }

    public function columns(): array
    {
        return [
            Column::make(__('Date'), 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::make(__('Action'), 'action_type_label', 'action_type')
                ->sortable()
                ->searchable(),

            Column::make(__('Status'), 'status_label', 'status')
                ->sortable(),

            Column::make(__('Location'), 'location_name'),

            Column::make(__('User'), 'user_name'),

            Column::make(__('Recipient'), 'recipient_name')
                ->searchable(),

            Column::make(__('Notes'), 'notes')
                ->bodyAttribute('whitespace-normal min-w-[300px] text-justify')
                ->sortable(),
        ];
    }
}
