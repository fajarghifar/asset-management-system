<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BorrowingItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'borrowing_id',
        'inventory_item_id',
        'quantity',
        'returned_quantity',
        'status',
        'returned_at',
        'condition_notes',
    ];

    protected $casts = [
        'returned_at' => 'datetime',
    ];

    public function borrowing()
    {
        return $this->belongsTo(Borrowing::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function getItemAttribute()
    {
        return $this->inventoryItem?->item;
    }

    public function getLocationAttribute()
    {
        return $this->inventoryItem?->location;
    }
}
