<?php

namespace App\Models\User;

use App\Models\BaseModel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends BaseModel implements JWTSubject, AuthenticatableContract, AuthorizableContract
{
    use Notifiable;
    use Authenticatable, Authorizable;


    protected $hidden = ['password', 'deleted'];


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'iss'    => env('JWT_ISSUER'),
            'userId' => $this->getKey(),
        ];
    }
}
