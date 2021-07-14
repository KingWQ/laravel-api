<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'user';
    public const CREATED_AT = 'add_time';
    public const UPDATED_AT = 'update_time';

    protected $fillable = [];

    protected $hidden = ['password','deleted'];

    protected $casts = [];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'iss'=>env('JWT_ISSUER'),
            'userId'=>$this->getKey(),
        ];
    }
}
