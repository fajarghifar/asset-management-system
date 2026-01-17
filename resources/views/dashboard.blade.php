<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-foreground leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <!-- Total Assets -->
                <div class="bg-card text-card-foreground p-4 rounded-lg shadow-sm border border-border">
                    <div class="flex flex-col">
                        <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Assets</span>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-2xl font-bold">{{ number_format($stats['total_assets']) }}</span>
                            <x-heroicon-o-cube class="w-4 h-4 text-blue-500" />
                        </div>
                    </div>
                </div>

                <!-- Maintenance -->
                <div class="bg-card text-card-foreground p-4 rounded-lg shadow-sm border border-border">
                    <div class="flex flex-col">
                        <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Under Maintenance</span>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-2xl font-bold">{{ number_format($stats['maintenance_assets']) }}</span>
                            <x-heroicon-o-wrench-screwdriver class="w-4 h-4 text-purple-500" />
                        </div>
                    </div>
                </div>

                <!-- Active Loans -->
                <div class="bg-card text-card-foreground p-4 rounded-lg shadow-sm border border-border">
                    <div class="flex flex-col">
                        <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Active Loans</span>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-2xl font-bold">{{ number_format($stats['active_loans']) }}</span>
                            <x-heroicon-o-clipboard-document-list class="w-4 h-4 text-amber-500" />
                        </div>
                    </div>
                </div>

                <!-- Pending Loans -->
                <div class="bg-card text-card-foreground p-4 rounded-lg shadow-sm border border-border">
                    <div class="flex flex-col">
                        <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Pending Requests</span>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-2xl font-bold">{{ number_format($stats['pending_loans']) }}</span>
                            <x-heroicon-o-clock class="w-4 h-4 text-sky-500" />
                        </div>
                    </div>
                </div>

                <!-- Overdue Loans -->
                <div class="bg-card text-card-foreground p-4 rounded-lg shadow-sm border border-border">
                    <div class="flex flex-col">
                        <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider text-red-600">Overdue Loans</span>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-2xl font-bold text-red-600">{{ number_format($stats['overdue_loans']) }}</span>
                            <x-heroicon-o-exclamation-circle class="w-4 h-4 text-red-600" />
                        </div>
                    </div>
                </div>

                <!-- Low Stock -->
                <div class="bg-card text-card-foreground p-4 rounded-lg shadow-sm border border-border">
                    <div class="flex flex-col">
                        <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider text-red-500">Low Stock Items</span>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-2xl font-bold text-red-500">{{ number_format($stats['low_stock_count']) }}</span>
                            <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-red-500" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Loan Status Distribution (Doughnut) -->
                <div class="lg:col-span-1 bg-card text-card-foreground p-6 rounded-lg shadow-sm border border-border flex flex-col">
                    <h3 class="text-lg font-semibold mb-4">Loan Status Breakdown</h3>
                    <div class="relative flex-1 min-h-[250px]"> <!-- Ensure height -->
                        <canvas id="loanStatusChart"></canvas>
                    </div>
                </div>

                <!-- Asset Status Distribution (Doughnut) -->
                <div class="lg:col-span-1 bg-card text-card-foreground p-6 rounded-lg shadow-sm border border-border flex flex-col">
                    <h3 class="text-lg font-semibold mb-4">Asset Status Breakdown</h3>
                    <div class="relative flex-1 min-h-[250px]">
                        <canvas id="assetStatusChart"></canvas>
                    </div>
                </div>

                <!-- Loans Trend (Line Chart for variety) -->
                <div class="lg:col-span-1 bg-card text-card-foreground p-6 rounded-lg shadow-sm border border-border flex flex-col">
                    <h3 class="text-lg font-semibold mb-4">Loan Requests Trend</h3>
                    <div class="relative flex-1 min-h-[250px]">
                        <canvas id="loanTrendChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Secondary Grid: Recent Activity & Low Stock List -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Activity -->
                <div class="bg-card text-card-foreground rounded-lg shadow-sm border border-border overflow-hidden">
                    <div class="p-6 border-b border-border">
                        <h3 class="text-lg font-semibold">Recent Activity</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-muted text-muted-foreground text-xs uppercase">
                                <tr>
                                    <th class="px-6 py-3">Code</th>
                                    <th class="px-6 py-3">Borrower</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3 text-right">Time</th>
                                    <th class="px-6 py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @forelse($recentLoans as $loan)
                                    <tr class="hover:bg-muted/50 transition-colors">
                                        <td class="px-6 py-4 font-medium text-xs">{{ $loan->code }}</td>
                                        <td class="px-6 py-4 font-medium">{{ $loan->borrower_name }}</td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                                {{ $loan->status === \App\Enums\LoanStatus::Approved ? 'bg-green-100 text-green-800' :
                                                    ($loan->status === \App\Enums\LoanStatus::Pending ? 'bg-yellow-100 text-yellow-800' :
                                                    ($loan->status === \App\Enums\LoanStatus::Overdue ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                                {{ $loan->status->getLabel() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right text-muted-foreground text-xs">
                                            {{ $loan->created_at->diffForHumans() }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <a href="{{ route('loans.show', $loan) }}" class="text-blue-600 hover:text-blue-800 transition-colors" title="View Details">
                                                <x-heroicon-o-eye class="w-5 h-5 mx-auto" />
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-muted-foreground">No recent activity.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Low Stock Items -->
                <div class="bg-card text-card-foreground rounded-lg shadow-sm border border-border overflow-hidden">
                    <div class="p-6 border-b border-border flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-red-600 dark:text-red-400">Low Stock Alerts</h3>
                        @if($stats['low_stock_count'] > 0)
                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $stats['low_stock_count'] }} Items</span>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-muted text-muted-foreground text-xs uppercase">
                                <tr>
                                    <th class="px-6 py-3">Product</th>
                                    <th class="px-6 py-3">Location</th>
                                    <th class="px-6 py-3 text-center">Qty</th>
                                    <th class="px-6 py-3 text-center">Min. Stock</th>
                                    <th class="px-6 py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @forelse($lowStockItems as $stock)
                                    <tr class="hover:bg-muted/50 transition-colors">
                                        <td class="px-6 py-4 font-medium">{{ $stock->product->name }}</td>
                                        <td class="px-6 py-4">
                                            <div class="font-medium">{{ $stock->location->name }} - {{ $stock->location->site->getLabel() }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center font-bold text-red-600">{{ $stock->quantity }}</td>
                                        <td class="px-6 py-4 text-center text-muted-foreground">{{ $stock->min_quantity }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <a href="{{ route('products.index') }}" class="text-blue-600 hover:text-blue-800 transition-colors" title="View Products">
                                                <x-heroicon-o-eye class="w-5 h-5 mx-auto" />
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-green-600 font-medium">All stock levels are healthy!</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Loan Status Chart
            const loanStatusCtx = document.getElementById('loanStatusChart').getContext('2d');
            new Chart(loanStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($loanStatus['labels']),
                    datasets: [{
                        data: @json($loanStatus['data']),
                        backgroundColor: [
                            '#eab308', // Pending (Yellow)
                            '#22c55e', // Approved (Green)
                            '#ef4444', // Rejected (Red)
                            '#64748b', // Closed (Slate)
                            '#b91c1c'  // Overdue (Dark Red)
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12 } }
                    },
                    cutout: '70%'
                }
            });

            // Asset Status Chart
            const assetCtx = document.getElementById('assetStatusChart').getContext('2d');
            new Chart(assetCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($assetStatus['labels']),
                    datasets: [{
                        data: @json($assetStatus['data']),
                        backgroundColor: [
                            '#22c55e', // Green
                            '#eab308', // Yellow
                            '#3b82f6', // Blue
                            '#f97316', // Orange
                            '#ef4444', // Red
                            '#64748b', // Slate
                            '#71717a'  // Zinc
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12 } }
                    },
                    cutout: '70%'
                }
            });

            // Loan Trend Chart
            const loanCtx = document.getElementById('loanTrendChart').getContext('2d');
            new Chart(loanCtx, {
                type: 'line', // Changed to line for better trend visualization
                data: {
                    labels: @json($loanChart['labels']),
                    datasets: [{
                        label: 'Loans',
                        data: @json($loanChart['data']),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                        x: { grid: { display: false } }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        });
    </script>
</x-app-layout>
