<?php

namespace App\Models\Promotion;

use App\Models\BaseModel;

class Groupon extends BaseModel
{

    protected $casts = [
        'discount' => 'float',
        'min'      => 'float',
    ];

}
