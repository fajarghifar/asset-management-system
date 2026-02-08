<?php

namespace App\Services;

use App\Enums\AssetStatus;
use App\Enums\LoanStatus;
use App\Models\Asset;
use App\Models\ConsumableStock;
use App\Models\Loan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    public function getStats(): array
    {
        return [
            'total_assets' => Asset::count(),
            'maintenance_assets' => Asset::where('status', AssetStatus::Maintenance)->count(),
            'active_loans' => Loan::where('status', LoanStatus::Approved)->count(),
            'pending_loans' => Loan::where('status', LoanStatus::Pending)->count(),
            'overdue_loans' => Loan::where('status', LoanStatus::Overdue)->count(),
            'closed_loans' => Loan::where('status', LoanStatus::Closed)->count(),
            'low_stock_count' => ConsumableStock::whereColumn('quantity', '<=', 'min_quantity')->count(),
        ];
    }

    public function getLoanStatusDistribution(): array
    {
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
    }

    public function getAssetStatusDistribution(): array
    {
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
    }

    public function getMonthlyLoanStats(): array
    {
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
