<?php

namespace App\Services\Promotion;

use App\CodeResponse;
use App\Enums\CouponEnums;
use App\Inputs\PageInput;
use App\Models\Promotion\Coupon;
use App\Models\Promotion\CouponUser;
use App\Services\BaseServices;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class CouponServices extends BaseServices
{
    public function getCoupon($id, $columns = ['*'])
    {
        return Coupon::query()->find($id, $columns);
    }

    public function countCoupon($couponId)
    {
        return CouponUser::query()->where('coupon_id', $couponId)->count('id');
    }

    public function countCouponByUserId($couponId, $userId)
    {
        return CouponUser::query()->where('coupon_id', $couponId)->where('user_id', $userId)->count('id');
    }

    public function getCoupons(array $couponIds, $columns = ['*'])
    {
        return Coupon::query()->whereIn('id', $couponIds)->get($columns);
    }

    public function list(PageInput $page, $columns = ['*'])
    {
        return Coupon::query()
            ->where('type', CouponEnums::TYPE_COMMON)
            ->where('status', CouponEnums::STATUS_NORMAL)
            ->orderBy($page->sort, $page->order)
            ->paginate($page->limit, $columns, 'page', $page->page);
    }

    public function mylist($userId, $status, PageInput $page, $columns = ['*'])
    {
        return CouponUser::query()
            ->when(!is_null($status),function(Builder $query) use($status){
                return $query->where('status', $status);
            })
            ->where('user_id', $userId)
            ->orderBy($page->sort, $page->order)
            ->paginate($page->limit, $columns, 'page', $page->page);
    }

    public function receive($userId, $couponId)
    {
        $coupon = CouponServices::getInstance()->getCoupon($couponId);
        if (is_null($coupon)) {
            return $this->throwBusinessException(CodeResponse::PARAM_ILLEGAL);
        }

        if ($coupon->total > 0) {
            $fetchedCount = CouponServices::getInstance()->countCoupon($couponId);
            if ($fetchedCount >= $coupon->total) {
                return $this->throwBusinessException(CodeResponse::COUPON_EXCEED_LIMIT);
            }
        }

        if ($coupon->limit > 0) {
            $userFetchedCount = CouponServices::getInstance()->countCouponByUserId($couponId, $userId);
            if ($userFetchedCount >= $coupon->limit) {
                return $this->throwBusinessException(CodeResponse::COUPON_EXCEED_LIMIT, '优惠券已经领取');
            }
        }

        if ($coupon->type != CouponEnums::TYPE_COMMON) {
            return $this->throwBusinessException(CodeResponse::COUPON_RECEIVE_FAIL, '优惠券类型不支持');
        }

        if ($coupon->status == CouponEnums::COUPON_STATUS_OUT) {
            return $this->throwBusinessException(CodeResponse::COUPON_EXCEED_LIMIT);
        }

        if ($coupon->status == CouponEnums::COUPON_STATUS_OUT) {
            return $this->throwBusinessException(CodeResponse::COUPON_RECEIVE_FAIL, '优惠券已经过期');
        }

        $couponUser = new CouponUser();
        if ($coupon->time_type == CouponEnums::COUPON_TIME_TYPE_TIME) {
            $startTime = $coupon->start_time;
            $endTime   = $coupon->end_time;
        } else {
            $startTime = Carbon::now();
            $endTime   = $startTime->copy()->addDays($coupon->days);
        }

        $couponUser->fill([
            'coupon_id'  => $couponId,
            'user_id'    => $userId,
            'start_time' => $startTime,
            'end_time'   => $endTime,
        ]);
        $couponUser->save();
    }

}
