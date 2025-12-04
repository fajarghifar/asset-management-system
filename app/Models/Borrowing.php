<?php

namespace App\Models;

use App\Enums\BorrowingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Borrowing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'borrower_name',
        'proof_image',
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

    public function items(): HasMany
    {
        return $this->hasMany(BorrowingItem::class);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === BorrowingStatus::Approved
            && $this->expected_return_date < now();
    }
}
