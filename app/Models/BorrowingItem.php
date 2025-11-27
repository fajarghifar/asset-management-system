<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BorrowingItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'returned_at' => 'datetime',
    ];

    public function borrowing()
    {
        return $this->belongsTo(Borrowing::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function fixedInstance()
    {
        return $this->belongsTo(FixedItemInstance::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
