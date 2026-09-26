<?php

namespace App\Models;

use App\Models\Artist;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class Client extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use Notifiable;

    protected $table = 'clients';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /*
    |--------------------------------------------------------------------------
    | JWT
    |--------------------------------------------------------------------------
    */

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Artist
    |--------------------------------------------------------------------------
    */

    public function artist(): HasOne
    {
        return $this->hasOne(
            Artist::class,
            'client_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getNameAttribute(): string
    {
        return trim(
            $this->first_name . ' ' . $this->last_name
        );
    }

    public function isArtist(): bool
    {
        return $this->role === 'artist';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
