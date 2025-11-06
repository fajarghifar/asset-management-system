<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstalledItemInstance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'item_id',
        'serial_number',
        'installed_location_id',
        'installed_at',
        'notes'
    ];

    protected $casts = [
        'installed_at' => 'date',
    ];

    // Relasi
    public function item()
    {
        return $this->belongsTo(Item::class)->withTrashed();
    }

    public function installedLocation()
    {
        return $this->belongsTo(Location::class, 'installed_location_id');
    }

    public function locationHistory()
    {
        return $this->hasMany(InstalledItemLocationHistory::class, 'instance_id');
    }
}
