<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KitItem extends Model
{
    protected $fillable = [
        'kit_id',
        'product_id',
        'location_id',
        'quantity',
        'notes',
    ];

    public function kit(): BelongsTo
    {
        return $this->belongsTo(Kit::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
