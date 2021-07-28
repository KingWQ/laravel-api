<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Models\Order\Cart;
use App\Services\Goods\GoodsServices;
use App\Services\Order\CartServices;

class CartController extends WxController
{
    protected $except = [];

    //加入购物车
    public function add()
    {
        $goodsId   = $this->verifyId('goodsId', 0);
        $productId = $this->verifyId('productId', 0);
        $number    = $this->verifyInteger('number', 0);
        if ($number <= 0) {
            return $this->badArgument();
        }

        $goods = GoodsServices::getInstance()->getGoods($goodsId);
        if (is_null($goods) || !$goods->is_on_sale) {
            return $this->fail(CodeResponse::GOODS_UNSHELVE);
        }

        $product = GoodsServices::getInstance()->getGoodsProductById($productId);
        if (is_null($product)) {
            return $this->badArgument();
        }

        $cartProduct = CartServices::getInstance()->getCartProduct($this->userId(), $goodsId, $productId);
        if (is_null($cartProduct)) {
            //add new cart product
            CartServices::getInstance()->newCart($this->userId(), $goods, $product, $number);
        } else {
            //edit cart product number
            $num = $cartProduct->number + $number;
            if ($num > $product->number) {
                return $this->fail(CodeResponse::GOODS_NO_STOCK);
            }
            $cartProduct->number = $num;
            $cartProduct->save();
        }

        $count = CartServices::getInstance()->countCartProduct($this->userId());

        return $this->success($count);
    }

    //获取购物车商品件数
    public function goodscount()
    {
        $count = CartServices::getInstance()->countCartProduct($this->userId());

        return $this->success($count);
    }

}
