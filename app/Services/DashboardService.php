<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Loan;
use App\Models\Asset;
use App\Enums\LoanStatus;
use App\Enums\AssetStatus;
use App\Models\ConsumableStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function getStats(): array
    {
        return Cache::remember('dashboard_stats', 600, function () {
            // Asset Stats
            $assetStats = Asset::selectRaw("
                count(*) as total,
                sum(case when status = ? then 1 else 0 end) as maintenance
            ", [AssetStatus::Maintenance->value])->first();

            // Loan Stats
            $loanStats = Loan::selectRaw("
                sum(case when status = ? then 1 else 0 end) as active,
                sum(case when status = ? then 1 else 0 end) as pending,
                sum(case when status = ? then 1 else 0 end) as overdue,
                sum(case when status = ? then 1 else 0 end) as closed
            ", [
                LoanStatus::Approved->value,
                LoanStatus::Pending->value,
                LoanStatus::Overdue->value,
                LoanStatus::Closed->value
            ])->first();

            // Low Stock
            $lowStockCount = ConsumableStock::whereColumn('quantity', '<=', 'min_quantity')->count();

            return [
                'total_assets' => $assetStats->total ?? 0,
                'maintenance_assets' => $assetStats->maintenance ?? 0,
                'active_loans' => $loanStats->active ?? 0,
                'pending_loans' => $loanStats->pending ?? 0,
                'overdue_loans' => $loanStats->overdue ?? 0,
                'closed_loans' => $loanStats->closed ?? 0,
                'low_stock_count' => $lowStockCount,
            ];
        });
    }

    public function getLoanStatusDistribution(): array
    {
        return Cache::remember('dashboard_loan_dist', 600, function () {
            $stats = Loan::select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $labels = array_map(fn($status) => $status->getLabel(), LoanStatus::cases());
            $data = [];

            foreach (LoanStatus::cases() as $status) {
                $data[] = $stats[$status->value] ?? 0;
            }

            return [
                'labels' => $labels,
                'data' => $data,
            ];
        });
    }

    public function getAssetStatusDistribution(): array
    {
        return Cache::remember('dashboard_asset_dist', 600, function () {
            $stats = Asset::select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $labels = array_map(fn($status) => $status->getLabel(), AssetStatus::cases());
            $data = [];

            foreach (AssetStatus::cases() as $status) {
                $data[] = $stats[$status->value] ?? 0;
            }

            return [
                'labels' => $labels,
                'data' => $data,
            ];
        });
    }

    public function getMonthlyLoanStats(): array
    {
        return Cache::remember('dashboard_monthly_loans', 600, function () {
            // Get last 6 months
            $loans = Loan::select(
                DB::raw('count(id) as total'),
                DB::raw("DATE_FORMAT(loan_date, '%Y-%m') as new_date"),
                DB::raw('YEAR(loan_date) as year, MONTH(loan_date) as month')
            )
                ->where('loan_date', '>=', Carbon::now()->subMonths(6))
                ->groupBy('year', 'month', 'new_date')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

            $labels = [];
            $data = [];

            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $monthKey = $date->format('Y-m');
                $labels[] = $date->translatedFormat('F Y');

                $record = $loans->firstWhere('new_date', $monthKey);
                $data[] = $record ? $record->total : 0;
            }

            return [
                'labels' => $labels,
                'data' => $data,
            ];
        });
    }

    public function getRecentActivity(int $limit = 5)
    {
        return Loan::with('user')
            ->latest()
            ->take($limit)
            ->get();
    }

    public function getLowStockItems(int $limit = 5)
    {
        return ConsumableStock::with('product', 'location')
            ->whereColumn('quantity', '<=', 'min_quantity')
            ->orderBy('quantity', 'asc')
            ->take($limit)
            ->get();
    }
}
