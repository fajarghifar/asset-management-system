<?php

namespace App\Filament\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class TopProductsChartWidget extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'topProductsChartWidget';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected function getHeading(): ?string
    {
        return __('widgets.charts.top_products');
    }

    protected static ?int $sort = 4;

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        // Calculate Top 5 Products using Collection to handle the polymorphic-like 'type' (Asset vs Consumable)
        $data = \App\Models\LoanItem::query()
             ->with(['asset.product', 'consumableStock.product'])
             ->get()
             ->map(fn (\App\Models\LoanItem $item) => $item->product_name)
             ->countBy()
             ->sortDesc()
             ->take(5);

        $categories = $data->keys()->toArray();
        $seriesData = $data->values()->toArray();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 4,
                    'horizontal' => true,
                ],
            ],
            'series' => [
                [
                    'name' => __('widgets.charts.loan_series'),
                    'data' => $seriesData,
                ],
            ],
            'xaxis' => [
                'categories' => $categories,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#10b981'],
        ];
    }
}
