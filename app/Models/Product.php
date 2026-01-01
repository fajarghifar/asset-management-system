<?php

namespace App\Models;

use App\Enums\ProductType;
use App\Models\ConsumableStock;
use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ObservedBy(ProductObserver::class)]
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'category_id',
        'can_be_loaned',
        'description'
    ];

    protected $casts = [
        'type' => ProductType::class,
        'can_be_loaned' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function consumableStocks(): HasMany
    {
        return $this->hasMany(ConsumableStock::class);
    }

    /**
     * Scope to eager load stock counts efficiently.
     */
    public function scopeWithStock($query)
    {
        return $query->withCount([
            'assets' => fn($q) => $q->where('status', \App\Enums\AssetStatus::InStock),
        ])->withSum('consumableStocks', 'quantity');
    }

    /**
     * Accessor to get total available stock dynamically.
     * Note: Requires `withStock()` scope to be efficient, otherwise triggers lazy loading.
     */
    public function getTotalStockAttribute(): int
    {
        if ($this->type === ProductType::Asset) {
            // Use loaded count if available, otherwise count manually
            return $this->assets_count ?? $this->assets()->where('status', \App\Enums\AssetStatus::InStock)->count();
        }

        // For consumables
        return (int) ($this->consumable_stocks_sum_quantity ?? $this->consumableStocks()->sum('quantity'));
    }
}
