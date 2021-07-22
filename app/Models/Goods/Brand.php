<?php

namespace App\Models\Goods;

use App\Models\BaseModel;

class Brand extends BaseModel
{
    protected $table = 'brand';
    public const CREATED_AT = 'add_time';
    public const UPDATED_AT = 'update_time';

    protected $fillable = [];

    protected $hidden = [];

    protected $casts = [
        'deleted'     => 'boolean',
        'floor_price' => 'float',
    ];

}
