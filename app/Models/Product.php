<?php

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'category_id',
        'can_be_loaned',
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

    public function getLabelAttribute(): string
    {
        return "{$this->name} ({$this->code})";
    }
}
