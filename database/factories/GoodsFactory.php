<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Goods\Goods;
use App\Models\Goods\GoodsProduct;
use App\Models\Goods\GoodsSpecification;
use Faker\Generator as Faker;


$factory->define(Goods::class, function (Faker $faker) {
    return [
        'goods_sn'      => $faker->word,
        'name'          => "测试商品" . $faker->word,
        'category_id'   => 1008009,
        'brand_id'      => 0,
        'gallery'       => [],
        'keywords'      => "",
        'brief'         => '测试',
        'is_on_sale'    => 1,
        'sort_order'    => $faker->numberBetween(1, 999),
        'pic_url'       => $faker->imageUrl,
        'share_url'     => $faker->url,
        'is_new'        => $faker->boolean,
        'is_hot'        => $faker->boolean,
        'unit'          => "件",
        'counter_price' => 919,
        'retail_price'  => 899,
        'detail'        => $faker->text
    ];
});

$factory->define(GoodsSpecification::class, function (Faker $faker) {
    return [
        'goods_id'      => 0,
        'specification' => '规格',
        'value'         => '标准',
    ];
});

$factory->define(GoodsProduct::class, function(Faker $faker){
    $goods = factory(Goods::class)->create();
    $spec = factory(GoodsSpecification::class)->create(['goods_id'=>$goods->id]);
    return [
        'goods_id'      => $goods->id,
        'specifications'=> [$spec->value],
        'price'         => 999,
        'number'        => 100,
        'url'           => $faker->imageUrl,
    ];
});
