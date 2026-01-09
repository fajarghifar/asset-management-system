<?php

namespace App\Models;

use App\Enums\AssetStatus;
use App\Enums\ProductType;
use App\Models\ConsumableStock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function getFullNameAttribute(): string
    {
        return "{$this->name} ({$this->code})";
    }

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
    /* -------------------------------------------------------------------------- */
    /*                                   Scopes                                   */
    /* -------------------------------------------------------------------------- */

    /**
     * Scope to load 'total_stock' as a virtual column efficiently.
     * Allows sorting: $query->orderBy('total_stock', 'desc')
     */
    public function scopeWithTotalStock(Builder $query): Builder
    {
        return $query->addSelect([
            'total_stock' => function ($subQuery) {
                $subQuery->selectRaw('
                CASE
                    WHEN products.type = ? THEN (
                        SELECT COUNT(*) FROM assets
                        WHERE assets.product_id = products.id
                        AND assets.status = ?
                    )
                    ELSE (
                        SELECT COALESCE(SUM(quantity), 0) FROM consumable_stocks
                        WHERE consumable_stocks.product_id = products.id
                    )
                END
            ', [ProductType::Asset->value, AssetStatus::InStock->value]);
            }
        ]);
    }
}
