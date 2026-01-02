<?php

return [
    'stats' => [
        'total_assets' => 'Total Assets',
        'total_assets_desc' => 'Total registered inventory units',
        'active_loans' => 'Active Loans',
        'active_loans_desc' => 'Currently being borrowed',
        'low_stock' => 'Low Stock',
        'low_stock_desc' => 'Need restocking',
        'overdue' => 'Overdue',
        'overdue_desc' => 'Past due date',
    ],
    'charts' => [
        'asset_status' => 'Asset Status Distribution',
        'monthly_loans' => 'Monthly Loan Trends',
        'top_products' => 'Top 5 Borrowed Items',
        'loan_series' => 'Loans count',
    ],
    'tables' => [
        'latest_loans' => 'Latest Loans',
        'low_stock' => 'Low Stock Alert',
        'columns' => [
            'borrower' => 'Borrower',
            'loan_date' => 'Loan Date',
            'due_date' => 'Due Date',
            'returned_date' => 'Returned Date',
            'status' => 'Status',
            'product' => 'Product Name',
            'site' => 'Site / Area',
            'location' => 'Location Detail',
            'remaining' => 'Remaining Stock',
            'min_limit' => 'Min Limit',
        ],
        'empty' => [
            'safe_stock' => 'Stock Safe',
            'safe_stock_desc' => 'No items below minimum stock.',
        ],
    ],
];
