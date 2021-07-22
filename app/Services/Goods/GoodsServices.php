<?php

namespace App\Services\Goods;

use App\Models\Goods\Goods;
use App\Services\BaseServices;
use Illuminate\Database\Eloquent\Builder;

class GoodsServices extends BaseServices
{
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
        $query = $this->getQueryByGoodsFilter($brandId, $isNew, $isHot, $keyword);
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
