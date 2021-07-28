<?php

namespace App\Models\Order;


use App\Models\BaseModel;

class Cart extends BaseModel
{
    protected $casts = [
        'checked'        => 'boolean',
        'specifications' => 'array',
    ];
}
