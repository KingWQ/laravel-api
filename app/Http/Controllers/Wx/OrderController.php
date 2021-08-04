<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Inputs\OrderSubmitInput;
use App\Services\Order\OrderServices;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderController extends WxController
{
    protected $except = [];


    //提交订单
    public function submit()
    {
        $input = OrderSubmitInput::new();

        $lockKey = sprintf('order_submit_%s_%s', $this->userId(), md5(serialize($input)));
        $lock    = Cache::lock($lockKey, 5);

        //加上锁，防止重复请求
        if (!$lock->get()) {
            return $this->fail(CodeResponse::FAIL, '请勿重复请求');
        }

        $order = DB::transaction(function () use ($input) {
            return OrderServices::getInstance()->submit($this->userId(), $input);
        });

        //释放锁
        $lock->release();

        return $this->success([
            'orderId'       => $order->id,
            'grouponLinkId' => $input->grouponLinkId ?? 0,
        ]);
    }

    //用户主动取消订单
    public function cancel()
    {
        $orderId = $this->verifyId('orderId');
        OrderServices::getInstance()->userCancel($this->userId(), $orderId);

        return $this->success();
    }

    //用户退款
    public function refund()
    {
        $orderId = $this->verifyId('orderId');
        OrderServices::getInstance()->refund($this->userId(), $orderId);

        return $this->success();
    }

    //用户确认收货
    public function confirm()
    {
        $orderId = $this->verifyId('orderId');
        OrderServices::getInstance()->confirm($this->userId(), $orderId);

        return $this->success();
    }

    //删除订单
    public function delete()
    {
        $orderId = $this->verifyId('orderId');
        OrderServices::getInstance()->delete($this->userId(), $orderId);

        return $this->success();
    }
}
