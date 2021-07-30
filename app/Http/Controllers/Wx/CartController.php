<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Services\Goods\GoodsServices;
use App\Services\Order\CartServices;

class CartController extends WxController
{
    protected $except = [];


    public function index()
    {
        $list               = CartServices::getInstance()->getValidCartList($this->userId());
        $goodsCount         = 0;
        $goodsAmount        = 0;
        $checkedGoodsCount  = 0;
        $checkedGoodsAmount = 0;

        foreach ($list as $item) {
            $goodsCount  += $item->number;
            $amount      = bcmul($item->price, $item->number, 2);
            $goodsAmount = bcadd($goodsAmount, $amount, 2);
            if ($item->checked) {
                $checkedGoodsCount  += $item->number;
                $checkedGoodsAmount = bcadd($checkedGoodsAmount, $amount, 2);
            }
        }

        return $this->success([
            'cartList'  => $list->toArray(),
            'cartTotal' => [
                'goodsCount'         => $goodsCount,
                'goodsAmount'        => (double)$goodsAmount,
                'checkedGoodsCount'  => $checkedGoodsCount,
                'checkedGoodsAmount' => (double)$checkedGoodsAmount,
            ]
        ]);
    }

    //立即购买
    public function fastadd()
    {
        $goodsId   = $this->verifyId('goodsId', 0);
        $productId = $this->verifyId('productId', 0);
        $number    = $this->verifyPositiveInteger('number', 0);

        $cart = CartServices::getInstance()->fastadd($this->userId(), $goodsId, $productId, $number);

        return $this->success($cart->id);
    }

    //加入购物车
    public function add()
    {
        $goodsId   = $this->verifyId('goodsId', 0);
        $productId = $this->verifyId('productId', 0);
        $number    = $this->verifyPositiveInteger('number', 0);

        CartServices::getInstance()->add($this->userId(), $goodsId, $productId, $number);
        $count = CartServices::getInstance()->countCartProduct($this->userId());

        return $this->success($count);
    }

    //获取购物车商品件数
    public function goodscount()
    {
        $count = CartServices::getInstance()->countCartProduct($this->userId());

        return $this->success($count);
    }

    public function update()
    {
        $id        = $this->verifyId('id', 0);
        $goodsId   = $this->verifyId('goodsId', 0);
        $productId = $this->verifyId('productId', 0);
        $number    = $this->verifyPositiveInteger('number', 0);

        $cart = CartServices::getInstance()->getCartById($this->userId(), $id);
        if (is_null($cart)) {
            return $this->badArgumentValue();
        }
        if ($cart->goods_id != $goodsId || $cart->product_id != $productId) {
            return $this->badArgumentValue();
        }

        $goods = GoodsServices::getInstance()->getGoods($goodsId);
        if (is_null($goods) || !$goods->is_on_sale) {
            return $this->fail(CodeResponse::GOODS_UNSHELVE);
        }

        $product = GoodsServices::getInstance()->getGoodsProductById($productId);
        if (is_null($product) || $product->number < $number) {
            return $this->fail(CodeResponse::GOODS_NO_STOCK);
        }

        $cart->number = $number;
        $ret          = $cart->save();

        return $this->failOrSuccess($ret);
    }

    public function delete()
    {
        $productIds = $this->verifyArrayNotEmpty('productIds', []);
        CartServices::getInstance()->delete($this->userId(), $productIds);

        return $this->index();
    }

    public function checked()
    {
        $productIds = $this->verifyArrayNotEmpty('productIds', []);
        $isChecked  = $this->verifyBoolean('isChecked');
        CartServices::getInstance()->updateChecked($this->userId(), $productIds, $isChecked == 1);

        return $this->index();

    }
}
