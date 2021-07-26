<?php

namespace App\Models\Goods;

use App\Models\BaseModel;

class Footprint extends BaseModel
{
    protected $table = 'footprint';

    protected $fillable = ['user_id','goods_id'];

    protected $hidden = [];

    protected $casts = [
        'deleted'       => 'boolean',
    ];

}
