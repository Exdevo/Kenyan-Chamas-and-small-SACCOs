<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'phone', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function member(): HasOne
    {
        return $this->hasOne(Member::class);
    }

    public function administeredChamas(): HasMany
    {
        return $this->hasMany(Chama::class, 'admin_user_id');
    }

    public function treasurerChamas(): HasMany
    {
        return $this->hasMany(Chama::class, 'treasurer_user_id');
    }

    public function confirmedContributions(): HasMany
    {
        return $this->hasMany(Contribution::class, 'confirmed_by');
    }

    public function reviewedLoans(): HasMany
    {
        return $this->hasMany(Loan::class, 'reviewed_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTreasurer(): bool
    {
        return $this->role === 'treasurer';
    }

    public function chama(): ?Chama
    {
        return $this->administeredChamas()->first()
            ?? $this->treasurerChamas()->first()
            ?? $this->member?->chama;
    }
}
