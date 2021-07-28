<?php

namespace App\Models\Promotion;

use App\Models\BaseModel;

class GrouponRules extends BaseModel
{

    protected $casts = [
        'discount' => 'float',
        'min'      => 'float',
    ];

}
