<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index(): View
    {
        return view('dashboard', [
            'stats' => $this->dashboardService->getStats(),
            'assetStatus' => $this->dashboardService->getAssetStatusDistribution(),
            'loanStatus' => $this->dashboardService->getLoanStatusDistribution(),
            'loanChart' => $this->dashboardService->getMonthlyLoanStats(),
            'recentLoans' => $this->dashboardService->getRecentActivity(),
            'lowStockItems' => $this->dashboardService->getLowStockItems(),
        ]);
    }
}
