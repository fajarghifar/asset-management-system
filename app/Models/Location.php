<?php

namespace App\Models;

use App\Models\Area;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'area_id',
        'is_borrowable',
        'description',
    ];

    protected $casts = [
        'is_borrowable' => 'boolean',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    // public function roomBookings()
    // {
    //     return $this->hasMany(RoomBooking::class);
    // }

    public function scopeBorrowable($query)
    {
        return $query->where('is_borrowable', true);
    }
}
