<?php

namespace App\Models\Goods;

use App\Models\BaseModel;

class Goods extends BaseModel
{
    protected $table = 'goods';

    protected $fillable = [];

    protected $hidden = [];

    protected $casts = [
        'deleted'       => 'boolean',
        'is_new'        => 'boolean',
        'is_hot'        => 'boolean',
        'counter_price' => 'float',
        'retail_price'  => 'float',
        'is_on_sale'    => 'boolean',
        'gallery'       => 'array',
    ];

}
