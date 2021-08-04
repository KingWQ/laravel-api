<?php

namespace Tests\Unit;

use App\CodeResponse;
use App\Enums\OrderEnums;
use App\Exceptions\BusinessException;
use App\Inputs\OrderSubmitInput;
use App\Jobs\OrderUnpaidTimeEndJob;
use App\Models\Goods\GoodsProduct;
use App\Models\Order\OrderGoods;
use App\Models\Promotion\GrouponRules;
use App\Models\User\User;
use App\Services\Order\CartServices;
use App\Services\Order\OrderServices;
use App\Services\User\AddressServices;
use App\Services\User\UserServices;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use DatabaseTransactions;

    public function testSubmit()
    {
        $this->user = factory(User::class)->state('address_default')->create();
        $address    = AddressServices::getInstance()->getAddressOrDefault($this->user->id);

        $product1 = factory(GoodsProduct::class)->create(['price' => 11.3]);
        $product2 = factory(GoodsProduct::class)->state('groupon')->create(['price' => 20.56]);
        $product3 = factory(GoodsProduct::class)->create(['price' => 10.6]);
        CartServices::getInstance()->add($this->user->id, $product1->goods_id, $product1->id, 2);
        CartServices::getInstance()->add($this->user->id, $product2->goods_id, $product2->id, 5);
        CartServices::getInstance()->add($this->user->id, $product3->goods_id, $product3->id, 3);
        CartServices::getInstance()->updateChecked($this->user->id, [$product1->id], false);

        $checkedGoodsList = CartServices::getInstance()->getCheckedCartList($this->user->id);

        $grouponPrice      = 0;
        $rulesId           = GrouponRules::query()->where('goods_id', $product2->goods_id)->first()->id ?? null;
        $checkedGoodsPrice = CartServices::getInstance()
            ->getCartPriceCutGroupon($checkedGoodsList, $rulesId, $grouponPrice);
        $this->assertEquals(129.6, $checkedGoodsPrice);

        $input = OrderSubmitInput::new([
            'addressId'      => $address->id,
            'cartId'         => 0,
            'grouponRulesId' => $rulesId,
            'couponId'       => 0,
            'message'        => '备注'
        ]);
        $order = OrderServices::getInstance()->submit($this->user->id, $input);
        $this->assertNotEmpty($order->id);
        $this->assertEquals($checkedGoodsPrice, $order->goods_price);
        $this->assertEquals($checkedGoodsPrice, $order->actual_price);
        $this->assertEquals($checkedGoodsPrice, $order->order_price);
        $this->assertEquals($grouponPrice, $order->groupon_price);
        $this->assertEquals('备注', $order->message);

        $list = OrderGoods::query()->where('order_id', $order->id)->get();
        $this->assertEquals(2, count($list));

        $productIds = CartServices::getInstance()->getCartList($this->user->id)->pluck('product_id')->toArray();
        $this->assertEquals([$product1->id], $productIds);

    }

    public function testReduceStock()
    {
        $product1 = factory(GoodsProduct::class)->create(['price' => 11.3]);
        $product2 = factory(GoodsProduct::class)->state('groupon')->create(['price' => 20.56]);
        $product3 = factory(GoodsProduct::class)->create(['price' => 10.6]);
        CartServices::getInstance()->add($this->user->id, $product1->goods_id, $product1->id, 2);
        CartServices::getInstance()->add($this->user->id, $product2->goods_id, $product2->id, 5);
        CartServices::getInstance()->add($this->user->id, $product3->goods_id, $product3->id, 3);
        CartServices::getInstance()->updateChecked($this->user->id, [$product1->id], false);
        $checkedGoodsList = CartServices::getInstance()->getCheckedCartList($this->user->id);

        OrderServices::getInstance()->reduceProductStock($checkedGoodsList);
        $this->assertEquals($product2->number - 5, $product2->refresh()->number);
        $this->assertEquals($product3->number - 3, $product3->refresh()->number);
    }

    public function testCancel()
    {
        $order = $this->getOrder();
        OrderServices::getInstance()->userCancel($this->user->id, $order->id);
        $this->assertEquals(OrderEnums::STATUS_CANCEL, $order->refresh()->order_status);
    }

    private function getOrder()
    {
        $this->user = factory(User::class)->state('address_default')->create();
        $address    = AddressServices::getInstance()->getAddressOrDefault($this->user->id);

        $product1 = factory(GoodsProduct::class)->create(['price' => 11.3]);
        $product2 = factory(GoodsProduct::class)->state('groupon')->create(['price' => 20.56]);
        $product3 = factory(GoodsProduct::class)->create(['price' => 10.6]);
        CartServices::getInstance()->add($this->user->id, $product1->goods_id, $product1->id, 2);
        CartServices::getInstance()->add($this->user->id, $product2->goods_id, $product2->id, 5);
        CartServices::getInstance()->add($this->user->id, $product3->goods_id, $product3->id, 3);
        CartServices::getInstance()->updateChecked($this->user->id, [$product1->id], false);
        $rulesId = GrouponRules::query()->where('goods_id', $product2->goods_id)->first()->id ?? null;
        $input   = OrderSubmitInput::new([
            'addressId'      => $address->id,
            'cartId'         => 0,
            'grouponRulesId' => $rulesId,
            'couponId'       => 0,
            'message'        => '备注'
        ]);
        $order   = OrderServices::getInstance()->submit($this->user->id, $input);

        return $order;
    }

    public function testCas()
    {
        $user = User::first(['id','nickname','mobile','update_time']);
        $user->nickname = 'test';
        $user->mobile = '15000000';
        $ret = $user->cas();
        dd($ret,$user);
    }
}
