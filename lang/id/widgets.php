<?php

return [
    'stats' => [
        'total_assets' => 'Total Aset',
        'total_assets_desc' => 'Unit terdaftar',
        'active_loans' => 'Peminjaman Aktif',
        'active_loans_desc' => 'Sedang dipinjam',
        'low_stock' => 'Stok Menipis',
        'low_stock_desc' => 'Perlu restock',
        'overdue' => 'Keterlambatan',
        'overdue_desc' => 'Melewati tempo',
    ],
    'charts' => [
        'asset_status' => 'Distribusi Status Aset',
        'monthly_loans' => 'Tren Peminjaman Bulanan',
        'top_products' => '5 Barang Paling Sering Dipinjam',
        'loan_series' => 'Jumlah Peminjaman',
    ],
    'tables' => [
        'latest_loans' => 'Peminjaman Terbaru',
        'low_stock' => 'Peringatan Stok Menipis',
        'columns' => [
            'borrower' => 'Nama Peminjam',
            'loan_date' => 'Tanggal Pinjam',
            'due_date' => 'Tenggat Waktu',
            'returned_date' => 'Tanggal Kembali',
            'status' => 'Status',
            'product' => 'Nama Barang',
            'site' => 'Gedung / Area',
            'location' => 'Lokasi Detail',
            'remaining' => 'Sisa Stok',
            'min_limit' => 'Batas Minimum',
        ],
        'empty' => [
            'safe_stock' => 'Stok Aman',
            'safe_stock_desc' => 'Tidak ada barang yang stoknya di bawah batas minimum.',
        ],
    ],
];
