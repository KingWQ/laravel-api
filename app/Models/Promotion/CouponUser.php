<?php

namespace App\Models\Promotion;

use App\Models\BaseModel;

class CouponUser extends BaseModel
{
    protected $fillable = ['user_id', 'coupon_id', 'start_time', 'end_time'];

}
