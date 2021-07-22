<?php

namespace App\Models\User;

use App\Models\BaseModel;

class Address extends BaseModel
{
    protected $table = 'address';
    public const CREATED_AT = 'add_time';
    public const UPDATED_AT = 'update_time';

    protected $fillable = [];

    protected $hidden = [];

    protected $casts = [
        'deleted'    => 'boolean',
        'is_default' => 'boolean',
    ];

}
