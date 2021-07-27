<?php

namespace App\Models\Promotion;

use App\Models\BaseModel;

class Coupon extends BaseModel
{
    protected $table = 'coupon';

    protected $fillable = [];

    protected $hidden = [];

    protected $casts = [
        'deleted'  => 'boolean',
        'discount' => 'float',
        'min'      => 'float',
    ];

}
