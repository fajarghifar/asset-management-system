<?php

namespace App\Models;

use App\Models\Asset;
use App\Models\ConsumableStock;
use App\Enums\ProductType;
use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
