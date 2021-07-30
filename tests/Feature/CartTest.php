<?php

namespace Tests\Feature;

use App\Models\Goods\GoodsProduct;
use App\Models\User\User;
use App\Services\Order\CartServices;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CartTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $product;
    private $authHeader;

    public function setUp(): void
    {
        parent::setUp();
        $this->user       = factory(User::class)->create();
        $this->product    = factory(GoodsProduct::class)->create(['number' => 10]);
        $this->authHeader = $this->getAuthHeader($this->user->username, 123456);
    }

    public function testFastadd()
    {
        $response = $this->post('wx/cart/add', [
            'goodsId'   => $this->product->goods_id,
            'productId' => $this->product->id,
            'number'    => 2,
        ], $this->authHeader);
        $response->assertJson(["errno" => 0, "errmsg" => "成功", "data" => "2"]);

        $response = $this->post('wx/cart/fastadd', [
            'goodsId'   => $this->product->goods_id,
            'productId' => $this->product->id,
            'number'    => 5,
        ], $this->authHeader);
        $response->assertJson(["errno" => 0, "errmsg" => "成功"]);
        $cart = CartServices::getInstance()
            ->getCartProduct($this->user->id, $this->product->goods_id, $this->product->id);
        $this->assertEquals(5, $cart->number);
        $response->assertJson(["errno" => 0, "errmsg" => "成功","data"=>$cart->id]);

    }

    public function testAdd()
    {
        //productId是0 goodsId是0
        $response = $this->post('wx/cart/add', ['goodsId' => 0, 'productId' => 0, 'number' => 2], $this->authHeader);
        $response->assertJson(["errno" => 402, "errmsg" => "参数值不对"]);

        //验证库存不足
        $response = $this->post('wx/cart/add', [
            'goodsId'   => $this->product->goods_id,
            'productId' => $this->product->id,
            'number'    => 11,
        ], $this->authHeader);
        $response->assertJson(["errno" => 711, "errmsg" => "商品库存不足!"]);

        //添加一个新的商品到购物车
        $response = $this->post('wx/cart/add', [
            'goodsId'   => $this->product->goods_id,
            'productId' => $this->product->id,
            'number'    => 2,
        ], $this->authHeader);
        $response->assertJson(["errno" => 0, "errmsg" => "成功", "data" => "2"]);


        //添加原有商品到购物车
        $response = $this->post('wx/cart/add', [
            'goodsId'   => $this->product->goods_id,
            'productId' => $this->product->id,
            'number'    => 3,
        ], $this->authHeader);
        $response->assertJson(['errno' => 0, 'errmsg' => "成功", 'data' => "5"]);
        $cart = CartServices::getInstance()
            ->getCartProduct($this->user->id, $this->product->goods_id, $this->product->id);
        $this->assertEquals(5, $cart->number);


        //多次添加商品验证库存不足
        $response = $this->post('wx/cart/add', [
            'goodsId'   => $this->product->goods_id,
            'productId' => $this->product->id,
            'number'    => 6,
        ], $this->authHeader);
        $response->assertJson(["errno" => 711, "errmsg" => "商品库存不足!"]);
    }

    public function testUpdate()
    {
        $response = $this->post('wx/cart/add', [
            'goodsId'   => $this->product->goods_id,
            'productId' => $this->product->id,
            'number'    => 2,
        ], $this->authHeader);
        $response->assertJson(["errno" => 0, "errmsg" => "成功", "data" => "2"]);
        $cart = CartServices::getInstance()->getCartProduct($this->user->id,
            $this->product->goods_id,$this->product->id);

        $response = $this->post('wx/cart/update', [
            'id'=>$cart->id,
            'goodsId'   => $this->product->goods_id,
            'productId' => $this->product->id,
            'number'    => 6,
        ], $this->authHeader);
        $response->assertJson(["errno" => 0, "errmsg" => "成功"]);

        $response = $this->post('wx/cart/update', [
            'id'=>$cart->id,
            'goodsId'   => $this->product->goods_id,
            'productId' => $this->product->id,
            'number'    => 11,
        ], $this->authHeader);
        $response->assertJson(["errno" => 711, "errmsg" => "商品库存不足!"]);


        $response = $this->post('wx/cart/update', [
            'id'=>$cart->id,
            'goodsId'   => $this->product->goods_id,
            'productId' => $this->product->id,
            'number'    => 0,
        ], $this->authHeader);
        $response->assertJson(["errno" => 402]);

    }

    public function testDelete()
    {
        $response = $this->post('wx/cart/add', [
            'goodsId'   => $this->product->goods_id,
            'productId' => $this->product->id,
            'number'    => 2,
        ], $this->authHeader);
        $response->assertJson(["errno" => 0, "errmsg" => "成功", "data" => "2"]);

        $cart = CartServices::getInstance()->getCartProduct($this->user->id,
            $this->product->goods_id,$this->product->id);
        $this->assertNotNull($cart);

        $response = $this->post('wx/cart/delete', [
            'productIds' => [$this->product->id],
        ], $this->authHeader);

        $cart = CartServices::getInstance()->getCartProduct($this->user->id,
            $this->product->goods_id,$this->product->id);
        $this->assertNull($cart);

        $response = $this->post('wx/cart/delete', [
            'productIds' => [],
        ], $this->authHeader);
        $response->assertJson(["errno" => 402]);

    }

    public function testChecked()
    {
        $response = $this->post('wx/cart/add', [
            'goodsId'   => $this->product->goods_id,
            'productId' => $this->product->id,
            'number'    => 2,
        ], $this->authHeader);
        $response->assertJson(["errno" => 0, "errmsg" => "成功", "data" => "2"]);

        $cart = CartServices::getInstance()->getCartProduct($this->user->id,
            $this->product->goods_id,$this->product->id);

        $this->assertTrue($cart->checked);

        $response = $this->post('wx/cart/checked', [
            'productIds' => [$this->product->id],
            'isChecked'=>0,
        ], $this->authHeader);

        $cart = CartServices::getInstance()->getCartProduct($this->user->id,
            $this->product->goods_id,$this->product->id);

        $this->assertFalse($cart->checked);

        $response = $this->post('wx/cart/checked', [
            'productIds' => [$this->product->id],
            'isChecked'=>1,
        ], $this->authHeader);

        $cart = CartServices::getInstance()->getCartProduct($this->user->id,
            $this->product->goods_id,$this->product->id);

        $this->assertTrue($cart->checked);
    }
}
