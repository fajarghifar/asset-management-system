<?php

namespace App\Models;

use App\Models\User;
use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $user_id
 * @property string $borrower_name
 * @property string $code
 * @property string|null $proof_image
 * @property string|null $purpose
 * @property \Illuminate\Support\Carbon $loan_date
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property \Illuminate\Support\Carbon|null $returned_date
 * @property LoanStatus $status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LoanItem> $loanItems
 */
class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'borrower_name',
        'code',
        'proof_image',
        'purpose',
        'loan_date',
        'due_date',
        'returned_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'loan_date' => 'datetime',
        'due_date' => 'datetime',
        'returned_date' => 'datetime',
        'status' => LoanStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loanItems(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    /**
     * Scope for loans that are still in progress (Approved or Overdue).
     */
    public function scopeOngoing(Builder $query): Builder
    {
        return $query->whereIn('status', [LoanStatus::Approved, LoanStatus::Overdue]);
    }

    /**
     * Scope for overdue loans.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', LoanStatus::Overdue)
            ->orWhere(fn($q) => $q->where('status', LoanStatus::Approved)->where('due_date', '<', now()));
    }
}
