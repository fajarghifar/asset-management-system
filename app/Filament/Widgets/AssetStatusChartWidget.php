<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class AssetStatusChartWidget extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'assetStatusChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected function getHeading(): ?string
    {
        return __('widgets.charts.asset_status');
    }

    /**
     * Sort Order
     *
     * @var int|null
     */
    protected static ?int $sort = 3;

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        // Get data grouped by status
        $data = Asset::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();

        // Prepare labels and series
        $labels = $data->map(fn($item) => $item->status->getLabel())->toArray();
        $series = $data->map(fn($item) => $item->count)->toArray();

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 300,
            ],
            'series' => $series,
            'labels' => $labels,
            'legend' => [
                'position' => 'bottom',
                'fontFamily' => 'inherit',
            ],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'size' => '50%',
                        'labels' => [
                            'show' => true,
                            'total' => [
                                'show' => true,
                                'label' => 'Total',
                                'fontFamily' => 'inherit',
                            ],
                        ],
                    ],
                ],
            ],
            'colors' => ['#22c55e', '#eab308', '#ef4444', '#cbd5e1', '#64748b'],
            'dataLabels' => [
                'enabled' => true,
            ],
            'stroke' => [
                'width' => 0,
            ],
        ];
    }
}
