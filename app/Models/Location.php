<?php

namespace App\Models;

use App\Enums\LocationSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'site',
        'name',
        'description',
    ];

    protected $casts = [
        'site' => LocationSite::class,
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->site->getLabel()} - {$this->name}";
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function consumableStocks(): HasMany
    {
        return $this->hasMany(ConsumableStock::class);
    }

    public function kitItems(): HasMany
    {
        return $this->hasMany(KitItem::class);
    }
}
