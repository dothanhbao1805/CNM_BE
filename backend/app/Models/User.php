<?php

namespace App\Models;

use App\Casts\EnumCast;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'email',
        'email_verified_at',
        'password',
        'full_name',
        'avatar',
        'gender',
        'phone_number',
        'role',
        'status',
        'is_verified',
        'provider',
        'provider_id',
    ];

    protected $hidden = [
        'password',
        // 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'gender' => 'boolean',
        'is_verified' => 'boolean',
        'role' => EnumCast::class . ':' . UserRole::class,
        'status' => EnumCast::class . ':' . UserStatus::class,
    ];

    /* ================= RELATIONSHIPS ================= */

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlist()
    {
        return $this->hasOne(Wishlist::class);
    }

    /* ================= HELPERS ================= */

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    /* ================= JWT ================= */

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,
            'email' => $this->email,
        ];
    }
}
