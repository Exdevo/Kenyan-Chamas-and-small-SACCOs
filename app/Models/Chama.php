<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Chama extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'monthly_amount', 'meeting_day', 'admin_user_id', 'treasurer_user_id'];

    protected function casts(): array
    {
        return ['monthly_amount' => 'decimal:2', 'meeting_day' => 'integer'];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function treasurer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'treasurer_user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function contributions(): HasManyThrough
    {
        return $this->hasManyThrough(Contribution::class, Member::class);
    }

    public function loans(): HasManyThrough
    {
        return $this->hasManyThrough(Loan::class, Member::class);
    }
}
