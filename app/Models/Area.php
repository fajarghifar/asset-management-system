<?php

namespace App\Models;

use App\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'address',
    ];

    protected $casts = [
        'category' => 'string',
    ];

    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
