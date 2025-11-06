<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstalledItemLocationHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'instance_id',
        'location_id',
        'installed_at',
        'removed_at',
        'notes',
    ];

    protected $casts = [
        'installed_at' => 'date',
        'removed_at' => 'date',
    ];

    // Relasi
    public function instance()
    {
        return $this->belongsTo(InstalledItemInstance::class, 'instance_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
