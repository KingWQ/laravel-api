<?php

namespace App\Http\Controllers\Wx;

use App\Inputs\OrderSubmitInput;
use App\Services\Order\OrderServices;
use Illuminate\Support\Facades\DB;

class OrderController extends WxController
{
    protected $except = [];


    public function submit()
    {
        $input = OrderSubmitInput::new();
        $order = DB::transaction(function () use ($input) {
            return OrderServices::getInstance()->submit($this->userId(), $input);
        });

        return $this->success([
            'orderId'       => $order->id,
            'grouponLinkId' => $input->grouponLinkId ?? 0,
        ]);
    }
}
