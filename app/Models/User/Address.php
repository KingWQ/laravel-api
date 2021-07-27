<?php

namespace App\Models\User;

use App\Models\BaseModel;

class Address extends BaseModel
{

    protected $casts = [
        'is_default' => 'boolean',
    ];

}
