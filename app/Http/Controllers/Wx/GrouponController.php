<?php

namespace App\Http\Controllers\Wx;

use App\Inputs\PageInput;
use App\Models\Promotion\GrouponRules;
use App\Services\Goods\GoodsServices;
use App\Services\Promotion\GrouponServices;

class GrouponController extends WxController
{
    protected $except = ['list'];

    //优惠券列表
    public function list()
    {
        $page = PageInput::new();
        $list = GrouponServices::getInstance()->getGrouponRules($page);

        $rules     = collect($list->items());
        $goodsIds  = $rules->pluck('goods_id')->toArray();
        $goodsList = GoodsServices::getInstance()->getGoodsListByIds($goodsIds)->keyBy('id');

        $voList = $rules->map(function (GrouponRules $rule) use ($goodsList) {
            $goods = $goodsList->get($rule->goods_id);

            return [
                'id'              => $goods->id,
                'name'            => $goods->name,
                'breif'           => $goods->breif,
                'picUrl'          => $goods->pic_url,
                'counterPrice'    => $goods->counter_price,
                'retailPrice'     => $goods->retail_price,
                'grouponPrice'    => bcsub($goods->retail_price, $rule->discount,2),
                'grouponDiscount' => $rule->discount,
                'grouponMember'   => $rule->discount_member,
                'expireTime'      => $rule->expire_time,
            ];
        });
        $list   = $this->paginate($list, $voList);

        return $this->success($list);
    }

}
