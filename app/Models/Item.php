<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'type',
        'description'
    ];

    protected $casts = [
        'type' => 'string',
    ];

    // Relational
    public function stocks()
    {
        return $this->hasMany(ItemStock::class);
    }

    public function fixedInstances()
    {
        return $this->hasMany(FixedItemInstance::class);
    }

    public function installedInstances()
    {
        return $this->hasMany(InstalledItemInstance::class);
    }

    // Scope
    public function scopeFixed($query)
    {
        return $query->where('type', 'fixed');
    }
    public function scopeConsumable($query)
    {
        return $query->where('type', 'consumable');
    }
    public function scopeInstalled($query)
    {
        return $query->where('type', 'installed');
    }
}
