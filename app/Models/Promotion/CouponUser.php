<?php

namespace App\Models\Promotion;

use App\Models\BaseModel;

class CouponUser extends BaseModel
{
    protected $table = 'coupon_user';

    protected $fillable = ['user_id', 'coupon_id', 'start_time', 'end_time'];

    protected $hidden = [];

    protected $casts = [
        'deleted' => 'boolean',
    ];

}
