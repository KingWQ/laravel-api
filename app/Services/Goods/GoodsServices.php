<?php

namespace App\Services\Goods;

use App\Models\Goods\Footprint;
use App\Models\Goods\Goods;
use App\Models\Goods\GoodsAttribute;
use App\Models\Goods\GoodsProduct;
use App\Models\Goods\GoodsSpecification;
use App\Models\Goods\Issue;
use App\Services\BaseServices;
use Illuminate\Database\Eloquent\Builder;

class GoodsServices extends BaseServices
{
    public function getGoods(int $goodsId)
    {
        return Goods::query()->find($goodsId);
    }

    public function getGoodsAttribute(int $goodsId)
    {
        return GoodsAttribute::query()->where('goods_id', $goodsId)->where('deleted', 0)->get();
    }

    public function getGoodsSpecification(int $goodsId)
    {
        $spec = GoodsSpecification::query()->where('goods_id', $goodsId)->get()->groupBy('specification');

        return $spec->map(function ($v, $k) {
            return ['name' => $k, 'valueList' => $v];
        })->values();
    }

    public function getGoodsProduct(int $goodsId)
    {
        return GoodsProduct::query()->where('goods_id', $goodsId)->get();
    }

    public function getGoodsIssue(int $page = 1, int $limit = 4)
    {
        return Issue::query()->forPage($page, $limit)->get();
    }

    //保存用户浏览商品足迹
    public function saveFootprint($userId,$goodsId)
    {
        $footPrint = new Footprint();
        $footPrint->fill(['user_id'=>$userId,'goods_id'=>$goodsId]);
        return $footPrint->save();
    }


    //获取在售商品的数量
    public function countGoodsOnSales()
    {
        return Goods::query()->where('is_on_sale', 1)->where('deleted', 0)->count('id');
    }

    public function listGoods(
        $categoryId,
        $brandId,
        $isNew,
        $isHot,
        $keyword,
        $columns,
        $sort = 'add_time',
        $order = 'desc',
        $page = 1,
        $limit = 10
    ) {
        $query = $this->getQueryByGoodsFilter($brandId, $isNew, $isHot, $keyword);

        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        return $query->orderBy($sort, $order)->paginate($limit, $columns, 'page', $page);
    }

    public function list2Category($brandId, $isNew, $isHot, $keyword)
    {
        $query       = $this->getQueryByGoodsFilter($brandId, $isNew, $isHot, $keyword);
        $categoryIds = $query->select(['category_id'])->pluck('category_id')->unique()->toArray();

        return CategoryServices::getInstance()->getL2ListByIds($categoryIds);
    }

    private function getQueryByGoodsFilter($brandId, $isNew, $isHot, $keyword)
    {
        $query = Goods::query()->where('is_on_sale', 1)->where('deleted', 0);

        if (!empty($brandId)) {
            $query->where('brand_id', $brandId);
        }
        if (!empty($isNew)) {
            $query->where('is_new', $isNew);
        }
        if (!empty($isHot)) {
            $query->where('is_hot', $isHot);
        }
        if (!empty($keyword)) {
            $query->where(function (Builder $query) use ($keyword) {
                $query->where('keywords', 'like', "%$keyword%")->orWhere('name', 'like', "%$keyword%");
            });
        }

        return $query;
    }
}
