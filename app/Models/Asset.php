<?php

namespace App\Models;

use App\Enums\AssetStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $product_id
 * @property int $location_id
 * @property string $asset_tag
 * @property string|null $serial_number
 * @property AssetStatus $status
 * @property \Illuminate\Support\Carbon|null $purchase_date
 * @property int $purchase_price
 * @property string|null $supplier_name
 * @property string|null $order_number
 * @property string|null $image_path
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\Location $location
 */
class Asset extends Model
{
    use HasFactory;

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
