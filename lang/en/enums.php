<?php

return [
    'asset_action' => [
        'register' => 'Registration',
        'update' => 'Update Data',
        'move' => 'Move Location',
        'check_out' => 'Check Out',
        'check_in' => 'Check In',
        'deploy' => 'Deploy',
        'pull' => 'Pull / Withdraw',
        'report_broken' => 'Report Broken',
        'maintenance' => 'Start Maintenance',
        'repaired' => 'Maintenance Finished',
        'mark_as_lost' => 'Mark as Lost',
        'dispose' => 'Dispose',
        'audit' => 'Stock Opname',
    ],
    'asset_status' => [
        'in_stock' => 'Ready',
        'loaned' => 'Loaned',
        'installed' => 'Installed',
        'maintenance' => 'Maintenance',
        'broken' => 'Broken',
        'lost' => 'Lost',
        'disposed' => 'Disposed',
    ],
    'loan_status' => [
        'pending' => 'Pending Approval',
        'approved' => 'Approved (Active)',
        'rejected' => 'Rejected',
        'closed' => 'Closed (Returned)',
        'overdue' => 'Overdue',
    ],
    'location_site' => [
        'BT' => 'BT Batik Trusmi',
        'JMP1' => 'JMP 1',
        'JMP2' => 'JMP 2',
        'TGS' => 'TGS',
    ],
    'product_type' => [
        'asset' => 'Fixed Asset',
        'consumable' => 'Consumable',
    ],
];
