<?php

namespace App\Models\Goods;

use App\Models\BaseModel;

class GoodsProduct extends BaseModel
{
    protected $table = 'goods_product';

    protected $fillable = [];

    protected $hidden = [];

    protected $casts = [
        'deleted'        => 'boolean',
        'specifications' => 'array',
        'price'          => 'float',
    ];

}
