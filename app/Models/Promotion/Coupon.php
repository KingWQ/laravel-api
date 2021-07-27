<?php

namespace App\Models\Promotion;

use App\Models\BaseModel;

class Coupon extends BaseModel
{

    protected $casts = [
        'discount' => 'float',
        'min'      => 'float',
    ];

}
