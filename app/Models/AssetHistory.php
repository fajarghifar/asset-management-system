<?php

namespace App\Models;

use App\Models\User;
use App\Models\Asset;
use App\Models\Location;
use App\Enums\AssetAction;
use App\Enums\AssetStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'user_id',
        'recipient_name',
        'status',
        'location_id',
        'action_type',
        'notes'
    ];

    protected $casts = [
        'status' => AssetStatus::class,
        'action_type' => AssetAction::class,
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
