<?php

namespace App\Models\Goods;

use App\Models\BaseModel;

class Brand extends BaseModel
{
    protected $casts = [
        'floor_price' => 'float',
    ];

}
