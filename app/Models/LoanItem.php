<?php

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $loan_id
 * @property ProductType $type
 * @property int|null $asset_id
 * @property int|null $consumable_stock_id
 * @property int $quantity_borrowed
 * @property int $quantity_returned
 * @property \Illuminate\Support\Carbon|null $returned_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Loan $loan
 * @property-read Asset|null $asset
 * @property-read ConsumableStock|null $consumableStock
 * @property-read string $product_name
 * @property-read string $location_name
 */
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
    public function scopeWithProducts(Builder $query): Builder
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
     * WARNING: Ensure 'withProducts' scope is used to avoid N+1.
     */
    public function getProductNameAttribute(): string
    {
        if ($this->type === ProductType::Asset) {
            return $this->asset?->product?->name ?? 'Unknown Asset';
        }

        if ($this->type === ProductType::Consumable) {
            return $this->consumableStock?->product?->name ?? 'Unknown Consumable';
        }

        return 'Unknown Item';
    }

    /**
     * Accessor to get the location name nicely formatted.
     */
    public function getLocationNameAttribute(): string
    {
        $location = null;

        if ($this->type === ProductType::Asset) {
            $location = $this->asset?->location;
        } elseif ($this->type === ProductType::Consumable) {
            $location = $this->consumableStock?->location;
        }

        return $location ? "{$location->name} ({$location->site?->value})" : '-';
    }
}
