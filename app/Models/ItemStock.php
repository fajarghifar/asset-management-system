<?php

namespace App\Models;

use App\Models\Item;
use App\Observers\ItemStockObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(ItemStockObserver::class)]
class ItemStock extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'item_id',
        'location_id',
        'quantity',
        'min_quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'min_quantity' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class)->withTrashed();
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
