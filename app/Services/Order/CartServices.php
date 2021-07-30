<?php

namespace App\Services\Order;

use App\CodeResponse;
use App\Models\Goods\Goods;
use App\Models\Goods\GoodsProduct;
use App\Models\Order\Cart;
use App\Services\BaseServices;
use App\Services\Goods\GoodsServices;
use Illuminate\Support\Facades\Log;

class CartServices extends BaseServices
{
    public function getCartList($userId)
    {
        return Cart::query()->where('user_id', $userId)->get();
    }

    public function getValidCartList($userId)
    {
        $list     = $this->getCartList($userId);
        $goodsIds = $list->pluck('goods_id')->toArray();
        $goodsList = GoodsServices::getInstance()->getGoodsListByIds($goodsIds)->keyBy('id');
        $invalidCartIds = [];

        $list = $list->filter(function (Cart $cart) use ($goodsList, &$invalidCartIds){
            $goods = $goodsList->get($cart->goods_id);
            $isValid = !empty($goods) && $goods->is_on_sale;
            if(!$isValid){
                $invalidCartIds[] = $cart->id;
            }
            return $isValid;
        });
        $this->deleteCartList($invalidCartIds);
        return $list;
    }

    public function deleteCartList($ids)
    {
        if (empty($ids)) {
            return 0;
        }

        return Cart::query()->whereIn('id', $ids)->delete();
    }

    public function getCartById($userId, $id)
    {
        return Cart::query()->where('user_id', $userId)->where('id', $id)->first();
    }

    public function getCartProduct($userId, $goodsId, $productId)
    {
        return Cart::query()
            ->where('user_id', $userId)
            ->where('goods_id', $goodsId)
            ->where('product_id', $productId)
            ->first();
    }

    public function countCartProduct($userId)
    {
        return Cart::query()->where('user_id', $userId)->sum('number');
    }

    public function getGoodsInfo($goodsId, $productId)
    {
        $goods = GoodsServices::getInstance()->getGoods($goodsId);
        if (is_null($goods) || !$goods->is_on_sale) {
            $this->throwBusinessException(CodeResponse::GOODS_UNSHELVE);
        }

        $product = GoodsServices::getInstance()->getGoodsProductById($productId);
        if (is_null($product)) {
            $this->throwBusinessException(CodeResponse::GOODS_NO_STOCK);
        }

        return [$goods, $product];
    }

    //添加购物车
    public function add($userId, $goodsId, $productId, $number)
    {
        [$goods, $product] = $this->getGoodsInfo($goodsId, $productId);
        $cartProduct = $this->getCartProduct($userId, $goodsId, $productId);
        if (is_null($cartProduct)) {
            return $this->newCart($userId, $goods, $product, $number);
        } else {
            $number = $cartProduct->number + $number;

            return $this->editCart($cartProduct, $product, $number);
        }
    }

    //立即购买
    public function fastadd($userId, $goodsId, $productId, $number)
    {
        [$goods, $product] = $this->getGoodsInfo($goodsId, $productId);
        $cartProduct = $this->getCartProduct($userId, $goodsId, $productId);
        if (is_null($cartProduct)) {
            return $this->newCart($userId, $goods, $product, $number);
        } else {
            return $this->editCart($cartProduct, $product, $number);
        }
    }

    public function editCart($existCart, $product, $num)
    {
        if ($num > $product->number) {
            return $this->throwBusinessException(CodeResponse::GOODS_NO_STOCK);
        }
        $existCart->number = $num;
        $existCart->save();

        return $existCart;
    }

    public function newCart($userId, Goods $goods, GoodsProduct $product, $number)
    {
        if ($number > $product->number) {
            $this->throwBusinessException(CodeResponse::GOODS_NO_STOCK);
        }
        $cart                 = Cart::new();
        $cart->goods_sn       = $goods->goods_sn;
        $cart->goods_name     = $goods->name;
        $cart->pic_url        = $product->url ? : $goods->pic_url;
        $cart->price          = $product->price;
        $cart->specifications = $product->specifications;
        $cart->user_id        = $userId;
        $cart->checked        = true;
        $cart->number         = $number;
        $cart->product_id     = $product->id;
        $cart->goods_id       = $goods->id;
        $cart->save();

        return $cart;
    }

    public function delete($userId, $productIds)
    {
        return Cart::query()->where('user_id', $userId)->whereIn('product_id', $productIds)->delete();
    }

    public function updateChecked($userId, $productIds, $isChecked)
    {
        return Cart::query()
            ->where('user_id', $userId)
            ->whereIn('product_id', $productIds)
            ->update(['checked' => $isChecked]);
    }
}
