<?php

namespace App\Services\Order;

use App\CodeResponse;
use App\Constant;
use App\Enums\OrderEnums;
use App\Inputs\OrderSubmitInput;
use App\Jobs\OrderUnpaidTimeEndJob;
use App\Models\Order\Order;
use App\Models\Order\OrderGoods;
use App\Notifications\NewPaidOrderEmailNotify;
use App\Notifications\NewPaidOrderSmsNotify;
use App\Services\BaseServices;
use App\Services\Goods\GoodsServices;
use App\Services\Promotion\CouponServices;
use App\Services\Promotion\GrouponServices;
use App\Services\SystemServices;
use App\Services\User\AddressServices;
use App\Services\User\UserServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class OrderServices extends BaseServices
{
    //生成订单
    public function submit($userId, OrderSubmitInput $input)
    {
        //1 验证团购活动是否有效
        if (!empty($input->grouponRulesId)) {
            GrouponServices::getInstance()->checkGrouponValid($userId, $input->grouponRulesId);
        }

        //2 验证收货地址
        $address = AddressServices::getInstance()->getAddressOrDefault($userId, $input->addressId);
        if (empty($address)) {
            return $this->throwBadArgumentValue();
        }

        //3 算价格
        //3.1 获取购物车的商品列表
        $checkedGoodsList = CartServices::getInstance()->getCheckedCartList($userId, $input->cartId);

        //3.2 计算订单总金额
        $grouponPrice      = 0;
        $checkedGoodsPrice = CartServices::getInstance()
            ->getCartPriceCutGroupon($checkedGoodsList, $input->grouponRulesId, $grouponPrice);

        //3.3 获取优惠券面额
        $couponPrice = 0;
        if ($input->couponId > 0) {
            $coupon     = CouponServices::getInstance()->getCoupon($input->couponId);
            $couponUser = CouponServices::getInstance()->getCouponUser($input->userCouponId);
            $is         = CouponServices::getInstance()->checkCouponAndPrice($coupon, $couponUser, $checkedGoodsPrice);
            if ($is) {
                $couponPrice = $coupon->discount;
            }
        }

        //3.4 运费
        $freightPrice = $this->getFreight($checkedGoodsPrice);

        //4 订单总费用
        $orderTotalPrice = bcadd($checkedGoodsPrice, $freightPrice, 2);
        $orderTotalPrice = bcsub($orderTotalPrice, $couponPrice, 2);
        $orderTotalPrice = max(0, $orderTotalPrice);

        //5 保存订单
        $order                 = new Order();
        $order->user_id        = $userId;
        $order->order_sn       = $this->generateOrderSn();
        $order->order_status   = OrderEnums::STATUS_CREATE;
        $order->consignee      = $address->name;
        $order->mobile         = $address->tel;
        $order->address        = $address->province . $address->city . $address->county . " " . $address->address_detail;
        $order->message        = $input->message ?? '';
        $order->goods_price    = $checkedGoodsPrice;
        $order->freight_price  = $freightPrice;
        $order->coupon_price   = $couponPrice;
        $order->order_price    = $orderTotalPrice;
        $order->integral_price = 0;
        $order->actual_price   = $orderTotalPrice;
        $order->groupon_price  = $grouponPrice;
        $order->save();

        //6 写入订单商品记录
        $this->saveOrderGoods($checkedGoodsList, $order->id);

        //7 删除购物车商品记录
        CartServices::getInstance()->clearCartGoods($userId, $input->cartId);

        //8 减库存
        $this->reduceProductStock($checkedGoodsList);

        //9 添加团购记录
        GrouponServices::getInstance()
            ->openOrJoinGroupon($userId, $order->id, $input->grouponRulesId, $input->grouponLinkId);

        //10 设置订单支付超时任务
        dispatch(new OrderUnpaidTimeEndJob($userId, $order->id));

        return $order;
    }

    //支付成功 处理订单
    public function payOrder(Order $order, $payId)
    {
        if (!$order->canPayHandle()) {
            $this->throwBusinessException(CodeResponse::ORDER_PAY_FAIL, '订单不能支付');
        }

        $order->pay_id       = $payId;
        $order->pay_time     = now()->toDateTimeString();
        $order->order_status = OrderEnums::STATUS_PAY;
        if ($order->cas() == 0) {
            $this->throwBusinessException(CodeResponse::UPDATED_FAIL);
        }

        //处理团购订单
        GrouponServices::getInstance()->payGrouponOrder($order->id);

        //发送邮箱给管理员
        Notification::route('mail', env('MAIL_USERNAME'))->notify(new NewPaidOrderEmailNotify($order->id));

        //发送短信给用户
        $user = UserServices::getInstance()->getUserById($order->user_id);
        $user->notify(new NewPaidOrderSmsNotify());

        return $order;
    }

    //订单发货
    public function ship($userId, $orderId, $shipSn, $shipChannel)
    {
        $order = $this->getOrderByUserIdAndId($userId, $orderId);
        if (empty($order)) {
            $this->throwBusinessException();
        }

        if (!$order->canShipHandle()) {
            $this->throwBusinessException(CodeResponse::ORDER_INVALID_OPERATION, '该订单不能发货');
        }

        $order->order_status = OrderEnums::STATUS_SHIP;
        $order->ship_sn      = $shipSn;
        $order->ship_channel = $shipChannel;
        $order->ship_time    = now()->toDateTimeString();
        if ($order->cas() == 0) {
            $this->throwBusinessException(CodeResponse::UPDATED_FAIL);
        }

        //todo 发通知给用户
        return $order;
    }

    //确认收货
    public function confirm($userId, $orderId, $isAuto = false)
    {
        $order = $this->getOrderByUserIdAndId($userId, $orderId);
        if (empty($order)) {
            $this->throwBusinessException();
        }
        if (!$order->canConfirmHandle()) {
            $this->throwBusinessException(CodeResponse::ORDER_INVALID_OPERATION, '该订单不能被确认收货');
        }

        $order->comments     = $this->countOrderGoods($orderId);
        $order->order_status = $isAuto ? OrderEnums::STATUS_AUTO_CONFIRM : OrderEnums::STATUS_CONFIRM;
        $order->confirm      = now()->toDateTimeString();
        if ($order->cas() == 0) {
            $this->throwBusinessException(CodeResponse::UPDATED_FAIL);
        }

        return $order;
    }

    //取消订单 并退款
    public function refund($userId, $orderId)
    {
        $order = $this->getOrderByUserIdAndId($userId, $orderId);

        if (empty($order)) {
            $this->throwBusinessException();
        }

        if (!$order->canRefundHandle()) {
            $this->throwBusinessException(CodeResponse::ORDER_INVALID_OPERATION, '该订单不能申请退款哦');
        }
        $order->order_status = OrderEnums::STATUS_REFUND;
        if ($order->cas() == 0) {
            $this->throwBusinessException(CodeResponse::UPDATED_FAIL);
        }

        //todo 发通知给管理员进行退款处理
        return $order;
    }

    //管理员同意退款
    public function agreeRefund(Order $order, $refundType, $refundContent)
    {
        if (!$order->canAgreeRefundHandle()) {
            $this->throwBusinessException(CodeResponse::ORDER_INVALID_OPERATION, '该订单不能同意退款');
        }

        $now                   = now()->toDateTimeString();
        $order->order_status   = OrderEnums::STATUS_REFUND_CONFIRM;
        $order->end_time       = $now;
        $order->refund_amount  = $order->actual_price;
        $order->refund_type    = $refundType;
        $order->refund_content = $refundContent;
        $order->refund_time    = $now;
        if ($order->cas() == 0) {
            $this->throwBusinessException(CodeResponse::UPDATED_FAIL);
        }
        $this->returnProductStock($order->id);

        return $order;
    }

    //删除订单
    public function delete($userId, $orderId)
    {
        $order = $this->getOrderByUserIdAndId($userId, $orderId);
        if (empty($order)) {
            $this->throwBusinessException();
        }
        if (!$order->canDeleteHandle()) {
            $this->throwBusinessException(CodeResponse::ORDER_INVALID_OPERATION, '该订单不能被删除哦');
        }
        $order->delete();

        //todo 处理订单售后的信息
        return true;
    }

    //计算订单中商品的数量
    public function countOrderGoods($orderId)
    {
        return OrderGoods::query()->where('order_id', $orderId)->count(['id']);
    }

    //返还库存
    public function returnProductStock($orderId)
    {
        $orderGoods = $this->getOrderGoodsList($orderId);
        foreach ($orderGoods as $goods) {
            $row = GoodsServices::getInstance()->addStock($goods->product_id, $goods->number);
            if ($row == 0) {
                $this->throwBusinessException(CodeResponse::UPDATED_FAIL);
            }
        }
    }

    public function reduceProductStock($goodsList)
    {
        $productIds = $goodsList->pluck('product_id')->toArray();
        $products   = GoodsServices::getInstance()->getGoodsProductByIds($productIds)->keyBy('id');
        foreach ($goodsList as $cart) {
            $product = $products->get($cart->product_id);
            if (empty($product)) {
                $this->throwBadArgumentValue();
            }
            if ($product->number < $cart->number) {
                $this->throwBusinessException(CodeResponse::GOODS_NO_STOCK);
            }
            $row = GoodsServices::getInstance()->reduceStock($product->id, $cart->number);
            if ($row == 0) {
                $this->throwBusinessException(CodeResponse::GOODS_NO_STOCK);
            }
        }
    }

    //保存订单快照
    private function saveOrderGoods($checkedGoodsList, $orderId)
    {
        foreach ($checkedGoodsList as $cart) {
            $orderGoods                 = OrderGoods::new();
            $orderGoods->order_id       = $orderId;
            $orderGoods->goods_id       = $cart->goods_id;
            $orderGoods->goods_sn       = $cart->goods_sn;
            $orderGoods->product_id     = $cart->product_id;
            $orderGoods->goods_name     = $cart->goods_name;
            $orderGoods->pic_url        = $cart->pic_url;
            $orderGoods->price          = $cart->price;
            $orderGoods->number         = $cart->number;
            $orderGoods->specifications = $cart->specifications;
            $orderGoods->save();
        }
    }

    //获取运费
    public function getFreight($price)
    {
        $freightPrice = 0;
        $freightMin   = SystemServices::getInstance()->getFreightMin();
        if (bccomp($freightMin, $price) == 1) {
            $freightPrice = SystemServices::getInstance()->getFreightValue();
        }

        return $freightPrice;
    }

    public function getOrderByUserIdAndId($userId, $orderId)
    {
        return Order::query()->where('user_id', $userId)->find($orderId);
    }

    public function getOrderGoodsList($orderId)
    {
        return OrderGoods::query()->where('order_id', $orderId)->get();
    }

    //取消订单
    public function userCancel($userId, $orderId)
    {
        DB::transaction(function () use ($userId, $orderId) {
            $this->cancel($userId, $orderId, 'user');
        });
    }

    public function systemCancel($userId, $orderId)
    {
        DB::transaction(function () use ($userId, $orderId) {
            $this->cancel($userId, $orderId, 'system');
        });
    }

    public function adminCancel($userId, $orderId)
    {
        DB::transaction(function () use ($userId, $orderId) {
            $this->cancel($userId, $orderId, 'admin');
        });
    }

    private function cancel($userId, $orderId, $role = 'user')
    {
        $order = $this->getOrderByUserIdAndId($userId, $orderId);
        if (is_null($order)) {
            $this->throwBadArgumentValue();
        }
        if (!$order->canCanelHandle()) {
            $this->throwBusinessException(CodeResponse::ORDER_INVALID_OPERATION, '订单不能取消');
        }
        switch ($role) {
            case 'system':
                $order->order_status = OrderEnums::STATUS_AUTO_CANCEL;
                break;
            case 'admin':
                $order->order_status = OrderEnums::STATUS_ADMIN_CANCEL;
                break;
            default:
                $order->order_status = OrderEnums::STATUS_CANCEL;
        }
        if ($order->cas() == 0) {
            $this->throwBusinessException(CodeResponse::UPDATED_FAIL);
        }

        $this->returnProductStock($orderId);

        return true;
    }

    //获取订单编号
    public function generateOrderSn()
    {
        return retry(5, function () {
            $orderSn = date('YmdHis') . Str::random(6);
            if (!$this->isOrderSnUsed($orderSn)) {
                return $orderSn;
            }
            Log::warning("订单号获取失败：orderSn" . $orderSn);
            $this->throwBusinessException(CodeResponse::FAIL, '订单号获取失败');
        });
    }


    //检查订单号是否有效
    private function isOrderSnUsed($orderSn)
    {
        return Order::query()->where('order_sn', $orderSn)->exists();
    }


}
