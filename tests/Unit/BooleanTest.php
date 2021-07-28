<?php

namespace Tests\Unit;

use App\Models\Goods\Goods;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BooleanTest extends TestCase
{
    use DatabaseTransactions;

    private $goodsId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->goodsId = Goods::query()->insertGetId([
            'goods_sn' => 'test',
            'category_id'=>1,
            'add_time'=>now(),
        ]);
    }

    //php artisan test tests/Unit/BooleanTest.php
    public function testSoltDeleteByModel()
    {
        $goods = Goods::query()->where('id', $this->goodsId)->first();
        $goods->delete();
//        $this->assertTrue($goods->deleted);
//        $goods = Goods::query()->where('id', $this->goodsId)->first();
//        $this->assertNull($goods);
//
//        $goods = Goods::onlyTrashed()->where('id', $this->goodsId)->first();
//        $goods->restore();
//        $this->assertFalse($goods->deleted);
//        $goods = Goods::query()->where('id', $this->goodsId)->first();
//        $this->assertEquals($this->goodsId, $goods->id ?? 0);
    }

//    public function testSoltDeleteByBuilder()
//    {
////        $ret = CouponUser::query()->where('id',2)->toSql();
////        $params = CouponUser::query()->where('id',2)->getBindings();
////        $ret = CouponUser::query()->where('id',2)->delete();
//
//        $goods = Goods::query()->where('id', $this->goodsId)->first();
//        $this->assertEquals($this->goodsId, $goods->id ?? 0);
//
//        //不包含已经被删除
//        $goods = Goods::withoutTrashed()->where('id', $this->goodsId)->first();
//        $this->assertEquals($this->goodsId, $goods->id ?? 0);
//
//        //软删除
//        $ret = Goods::query()->where('id', $this->goodsId)->delete();
//        $this->assertEquals(1, $ret);
//        $goods = Goods::query()->where('id', $this->goodsId)->first();
//        $this->assertNull($goods);
//
//        //获取已经被删除的
//        $goods = Goods::withTrashed()->where('id', $this->goodsId)->first();
//        $this->assertEquals($this->goodsId, $goods->id ?? 0);
//
//        //只拿已经被删除的
//        $goods = Goods::onlyTrashed()->where('id', $this->goodsId)->first();
//        $this->assertEquals($this->goodsId, $goods->id ?? 0);
//
//        //恢复把1改成0
//        $ret = Goods::withTrashed()->where('id', $this->goodsId)->restore();
//        $this->assertEquals(1, $ret);
//        $goods = Goods::onlyTrashed()->where('id', $this->goodsId)->first();
//        $this->assertNull($goods);
//        $goods = Goods::query()->where('id', $this->goodsId)->first();
//        $this->assertEquals($this->goodsId, $goods->id ?? 0);
//
//        //强制删除
//        $ret = Goods::query()->where('id', $this->goodsId)->forceDelete();
//        $this->assertEquals(1, $ret);
//        $goods = Goods::query()->where('id', $this->goodsId)->first();
//        $this->assertNull($goods);
//        $goods = Goods::onlyTrashed()->where('id', $this->goodsId)->first();
//        $this->assertNull($goods);
//
//
//    }
}
