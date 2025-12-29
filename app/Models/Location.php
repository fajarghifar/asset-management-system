<?php

namespace App\Models;

use App\Enums\LocationSite;
use App\Observers\LocationObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

// #[ObservedBy(LocationObserver::class)]
class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'site',
        'name',
        'code',
        'description',
    ];

    protected $casts = [
        'site' => LocationSite::class,
    ];
}
