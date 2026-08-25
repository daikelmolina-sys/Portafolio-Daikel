<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // -------------------------------------------------------------------------
    // ROLES
    // -------------------------------------------------------------------------
    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isCashier(): bool  { return $this->role === 'cashier'; }
    public function isCustomer(): bool { return $this->role === 'customer'; }

    // -------------------------------------------------------------------------
    // RELACIONES
    // -------------------------------------------------------------------------
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function processedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'cashier_id');
    }
}
