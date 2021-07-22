<?php

namespace App\Models\Goods;

use App\Models\BaseModel;

class Goods extends BaseModel
{
    protected $table = 'goods';
    public const CREATED_AT = 'add_time';
    public const UPDATED_AT = 'update_time';

    protected $fillable = [];

    protected $hidden = [];

    protected $casts = [
        'deleted'       => 'boolean',
        'is_new'        => 'boolean',
        'is_hot'        => 'boolean',
        'counter_price' => 'float',
        'retail_price'  => 'float',
    ];

}
