<?php

namespace App\Models;

use App\Enums\LoanItemType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'type',
        'asset_id',
        'consumable_stock_id',
        'quantity_borrowed',
        'quantity_returned',
        'returned_at',
    ];

    protected $casts = [
        'type' => LoanItemType::class,
        'returned_at' => 'datetime',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function consumableStock(): BelongsTo
    {
        return $this->belongsTo(ConsumableStock::class);
    }
}
