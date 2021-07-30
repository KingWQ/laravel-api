<?php

namespace App\Models\Order;


use App\Models\BaseModel;

class OrderGoods extends BaseModel
{
    protected $casts = [
        'specifications' => 'array',
    ];
}
