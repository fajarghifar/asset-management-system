<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Asset;
use App\Models\Loan;
use App\Models\ConsumableStock;
use App\Enums\LoanStatus;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make(__('widgets.stats.total_assets'), Asset::count())
                ->description(__('widgets.stats.total_assets_desc'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),

            Stat::make(__('widgets.stats.active_loans'), Loan::where('status', LoanStatus::Approved)->count())
                ->description(__('widgets.stats.active_loans_desc'))
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),

            Stat::make(__('widgets.stats.low_stock'), ConsumableStock::whereColumn('quantity', '<=', 'min_quantity')->count())
                ->description(__('widgets.stats.low_stock_desc'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make(__('widgets.stats.overdue'), Loan::overdue()->count())
                ->description(__('widgets.stats.overdue_desc'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
        ];
    }
}
