<?php

namespace App\Services\Order;

use App\CodeResponse;
use App\Constant;
use App\Enums\OrderEnums;
use App\Inputs\OrderSubmitInput;
use App\Models\Order\Order;
use App\Models\Order\OrderGoods;
use App\Services\BaseServices;
use App\Services\Promotion\CouponServices;
use App\Services\Promotion\GrouponServices;
use App\Services\SystemServices;
use App\Services\User\AddressServices;
use Illuminate\Support\Str;

class OrderServices extends BaseServices
{
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

        return $order;
    }

    public function reduceProductStock($goodsList)
    {

    }


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
