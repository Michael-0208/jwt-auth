<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Get the coordinates associated with the user.
     */
    public function coordinates()
    {
        return $this->hasMany(UserCoordinate::class);
    }

    /**
     * Get the identifier that will be stored in the JWT payload.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get custom JWT claims.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
