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


    public function submit()
    {
        $input = OrderSubmitInput::new();

        $lockKey = sprintf('order_submit_%s_%s', $this->userId(), md5(serialize($input)));
        $lock    = Cache::lock($lockKey, 5);
        if (!$lock->get()) {
            return $this->fail(CodeResponse::FAIL, '请勿重复请求');
        }

        $order = DB::transaction(function () use ($input) {
            return OrderServices::getInstance()->submit($this->userId(), $input);
        });

        return $this->success([
            'orderId'       => $order->id,
            'grouponLinkId' => $input->grouponLinkId ?? 0,
        ]);
    }

    //用户主动取消订单
    public function cancel()
    {
        $orderId = $this->verifyId('orderId');
        OrderServices::getInstance()->userCancel($this->userId(),$orderId);
        return $this->success();
    }
}
