<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class LoanRepayment extends Model
{
    protected $fillable = [
        'loan_id', 'amount', 'date', 'mpesa_code', 'status', 'confirmed_by', 'confirmed_at',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'date' => 'date', 'confirmed_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::updating(function (LoanRepayment $repayment) {
            if ($repayment->getOriginal('status') === 'confirmed') {
                throw ValidationException::withMessages([
                    'repayment' => 'Confirmed repayments are permanent and cannot be changed.',
                ]);
            }
        });

        static::deleting(function (LoanRepayment $repayment) {
            if ($repayment->status === 'confirmed') {
                throw ValidationException::withMessages([
                    'repayment' => 'Confirmed repayments are permanent and cannot be deleted.',
                ]);
            }
        });
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
