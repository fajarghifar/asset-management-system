<?php

namespace App\Models;

use App\Models\Area;
use App\Enums\ItemType;
use App\Models\InstalledItem;
use App\Models\InventoryItem;
use App\Observers\LocationObserver;
use App\Models\InstalledItemHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(LocationObserver::class)]
class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'area_id',
        'description',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function installedItem()
    {
        return $this->hasMany(InstalledItem::class);
    }

    public function installedItemHistory()
    {
        return $this->hasMany(InstalledItemHistory::class);
    }

    // --- HELPER ---
    public function hasConsumableStock(): bool
    {
        return $this->inventoryItems()
            ->whereHas('item', fn($q) => $q->where('type', ItemType::Consumable))
            ->where('quantity', '>', 0)
            ->exists();
    }

    public function hasFixedItems(): bool
    {
        return $this->inventoryItems()
            ->whereHas('item', fn($q) => $q->where('type', ItemType::Fixed))
            ->exists();
    }

    public function hasInstalledItems(): bool
    {
        return $this->installedItemInstances()->exists();
    }
}
