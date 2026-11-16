<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class Contribution extends Model
{
    protected $fillable = [
        'member_id', 'amount', 'month', 'payment_method', 'mpesa_code',
        'status', 'payment_date', 'confirmed_by', 'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'confirmed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (Contribution $contribution) {
            if ($contribution->getOriginal('status') === 'confirmed') {
                throw ValidationException::withMessages([
                    'contribution' => 'Confirmed contributions are permanent and cannot be changed.',
                ]);
            }
        });

        static::deleting(function (Contribution $contribution) {
            if ($contribution->status === 'confirmed') {
                throw ValidationException::withMessages([
                    'contribution' => 'Confirmed contributions are permanent and cannot be deleted.',
                ]);
            }
        });
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
