<?php

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'notes',
    ];

    protected $casts = [
        'type' => ProductType::class,
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

    /**
     * Scope to eager load related products for performance.
     */
    public function scopeWithProducts($query)
    {
        return $query->with([
            'asset.product',
            'asset.location',
            'consumableStock.product',
            'consumableStock.location'
        ]);
    }

    /**
     * Accessor to get the product name dynamically.
     */
    public function getProductNameAttribute(): string
    {
        return match ($this->type) {
            ProductType::Asset => $this->asset?->product?->name ?? 'Unknown Asset',
            ProductType::Consumable => $this->consumableStock?->product?->name ?? 'Unknown Consumable',
            default => 'Unknown Item',
        };
    }

    /**
     * Accessor to get the location name nicely formatted.
     */
    public function getLocationNameAttribute(): string
    {
        $location = match ($this->type) {
             ProductType::Asset => $this->asset?->location,
             ProductType::Consumable => $this->consumableStock?->location,
             default => null,
        };

        return $location ? "{$location->name} ({$location->site?->value})" : '-';
    }
}
