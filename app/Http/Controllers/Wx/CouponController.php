<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Constant;
use App\Inputs\PageInput;
use App\Models\Promotion\CouponUser;
use App\Services\Promotion\CouponServices;
use Illuminate\Support\Carbon;

class CouponController extends WxController
{
    protected $except = ['list'];

    //优惠券列表
    public function list()
    {
        $page    = PageInput::new();
        $columns = ['id', 'name', 'desc', 'tag', 'discount', 'min', 'days', 'start_time', 'end_time'];
        $list    = CouponServices::getInstance()->list($page, $columns);

        return $this->successPaginate($list);
    }

    //我的优惠券列表
    public function mylist()
    {
        $status         = $this->verifyInteger('status', 0);
        $page           = PageInput::new();
        $list           = CouponServices::getInstance()->mylist($this->userId(), $status, $page);
        $couponUserList = collect($list->items());
        $couponIds      = $couponUserList->pluck('coupon_id')->toArray();
        $coupons        = CouponServices::getInstance()->getCoupons($couponIds)->keyBy('id');
        $mylist         = $couponUserList->map(function (CouponUser $item) use ($coupons) {
            $coupon = $coupons->get($item->coupon_id);

            return [
                'id'        => $item->id,
                'cid'       => $coupon->id,
                'name'      => $coupon->name,
                'desc'      => $coupon->desc,
                'tag'       => $coupon->tag,
                'min'       => $coupon->min,
                'discount'  => $coupon->discount,
                'startTime' => $item->start_time,
                'endTime'   => $item->ene_time,
                'available' => false,
            ];
        });

        $list = $this->paginate($list, $mylist);

        return $this->success($list);
    }

    //优惠券领取
    public function receive()
    {
        $couponId = $this->verifyId('couponId', 0);
        CouponServices::getInstance()->receive($this->userId(), $couponId);

        return $this->success();
    }

}
