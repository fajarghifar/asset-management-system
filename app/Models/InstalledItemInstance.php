<?php

namespace App\Models;

use App\Models\Item;
use App\Models\Location;
use Illuminate\Database\Eloquent\Model;
use App\Models\InstalledItemLocationHistory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\InstalledItemInstanceObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(InstalledItemInstanceObserver::class)]
class InstalledItemInstance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'item_id',
        'serial_number',
        'current_location_id',
        'installed_at',
        'notes'
    ];

    protected $casts = [
        'installed_at' => 'date',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class)->withTrashed();
    }

    public function currentLocation()
    {
        return $this->belongsTo(Location::class, 'current_location_id');
    }

    public function locationHistory()
    {
        return $this->hasMany(InstalledItemLocationHistory::class, 'instance_id');
    }
}
