<?php

namespace Tests\Unit;

use App\CodeResponse;
use App\Exceptions\BusinessException;
use App\Models\Goods\GoodsProduct;
use App\Models\Promotion\GrouponRules;
use App\Models\User\User;
use App\Services\Order\CartServices;
use App\Services\User\UserServices;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CartTest extends TestCase
{
    use DatabaseTransactions;


    public function testGetCartPriceCutGrouponSimple()
    {
        $product1 = factory(GoodsProduct::class)->create(['price' => 11.3]);
        $product2 = factory(GoodsProduct::class)->create(['price' => 20.56]);
        $product3 = factory(GoodsProduct::class)->create(['price' => 10.6]);
        CartServices::getInstance()->add($this->user->id, $product1->goods_id, $product1->id, 2);
        CartServices::getInstance()->add($this->user->id, $product2->goods_id, $product2->id, 1);
        CartServices::getInstance()->add($this->user->id, $product3->goods_id, $product3->id, 1);
        CartServices::getInstance()->updateChecked($this->user->id, [$product3->id], false);

        $checkedGoodsList = CartServices::getInstance()->getCheckedCartList($this->user->id);

        $grouponPrice      = 0;
        $checkedGoodsPrice = CartServices::getInstance()->getCartPriceCutGroupon($checkedGoodsList, '', $grouponPrice);
        $this->assertEquals(43.16, $checkedGoodsPrice);
    }

    public function testGetCartPriceCutGrouponGroupon()
    {
        $product1 = factory(GoodsProduct::class)->create(['price' => 11.3]);
        $product2 = factory(GoodsProduct::class)->state('groupon')->create(['price' => 20.56]);
        $product3 = factory(GoodsProduct::class)->create(['price' => 10.6]);
        CartServices::getInstance()->add($this->user->id, $product1->goods_id, $product1->id, 2);
        CartServices::getInstance()->add($this->user->id, $product2->goods_id, $product2->id, 5);
        CartServices::getInstance()->add($this->user->id, $product3->goods_id, $product3->id, 3);
        CartServices::getInstance()->updateChecked($this->user->id, [$product1->id], false);

        $checkedGoodsList = CartServices::getInstance()->getCheckedCartList($this->user->id);

        $grouponPrice      = 0;
        $rulesId = GrouponRules::query()->where('goods_id',$product2->goods_id)->first()->id ?? null;
        $checkedGoodsPrice = CartServices::getInstance()->getCartPriceCutGroupon($checkedGoodsList, $rulesId, $grouponPrice);
        $this->assertEquals(129.6, $checkedGoodsPrice);
    }


}
