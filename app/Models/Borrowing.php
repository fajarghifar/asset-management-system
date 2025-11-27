<?php

namespace App\Models;

use App\Enums\BorrowingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Borrowing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'user_id',
        'purpose',
        'borrow_date',
        'expected_return_date',
        'actual_return_date',
        'status',
        'notes'
    ];

    protected $casts = [
        'borrow_date' => 'datetime',
        'expected_return_date' => 'datetime',
        'actual_return_date' => 'datetime',
        'status' => BorrowingStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(BorrowingItem::class);
    }

    // [UPDATE] Scopes menggunakan Enum
    public function scopePending($query)
    {
        return $query->where('status', BorrowingStatus::Pending);
    }

    public function scopeActive($query)
    {
        return $query->where('status', BorrowingStatus::Approved);
    }

    public function scopeOverdue($query)
    {
        return $query->whereNull('actual_return_date')
            ->where('expected_return_date', '<', now())
            ->where('status', BorrowingStatus::Approved);
    }
}
