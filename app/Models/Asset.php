<?php

namespace App\Models;

use App\Enums\AssetStatus;
use App\Observers\AssetObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(AssetObserver::class)]
class Asset extends Model
{
    use HasFactory;

    public bool $shouldLogHistory = true;

    protected $fillable = [
        'product_id',
        'location_id',
        'asset_tag',
        'serial_number',
        'status',
        'purchase_date',
        'purchase_price',
        'supplier_name',
        'order_number',
        'image_path',
        'notes',
    ];

    protected $casts = [
        'status' => AssetStatus::class,
        'purchase_date' => 'date',
        'purchase_price' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(AssetHistory::class)->latest();
    }

    public function latestHistory(): HasOne
    {
        return $this->hasOne(AssetHistory::class)->latestOfMany();
    }
}
