<?php

namespace App\Services\Goods;

use App\Inputs\GoodsListInput;
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
    public function reduceStock($productId, $number)
    {
        return GoodsProduct::query()->where('id',$productId)
            ->where('number','>=',$number)->decrement('number',$number);
    }

    public function getGoodsListByIds(array $ids)
    {
        if (empty($ids)) {
            return collect();
        }

        return Goods::query()->whereIn('id', $ids)->get();
    }

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

    public function getGoodsProductById(int $productId)
    {
        return GoodsProduct::query()->find($productId);
    }
    public function getGoodsProductByIds(array $productIds)
    {
        if(empty($productIds)) return collect();
        return GoodsProduct::query()->whereIn('id',$productIds)->get();
    }

    public function getGoodsIssue(int $page = 1, int $limit = 4)
    {
        return Issue::query()->forPage($page, $limit)->get();
    }

    //保存用户浏览商品足迹
    public function saveFootprint($userId, $goodsId)
    {
        $footPrint = new Footprint();
        $footPrint->fill(['user_id' => $userId, 'goods_id' => $goodsId]);

        return $footPrint->save();
    }


    //获取在售商品的数量
    public function countGoodsOnSales()
    {
        return Goods::query()->where('is_on_sale', 1)->where('deleted', 0)->count('id');
    }

    public function listGoods(GoodsListInput $input, $columns)
    {
        $query = $this->getQueryByGoodsFilter($input);

        if (!empty($categoryId)) {
            $query->where('category_id', $input->categoryId);
        }

        return $query->orderBy($input->sort, $input->order)->paginate($input->limit, $columns, 'page', $input->page);
    }

    public function list2Category(GoodsListInput $input)
    {
        $query       = $this->getQueryByGoodsFilter($input);
        $categoryIds = $query->select(['category_id'])->pluck('category_id')->unique()->toArray();

        return CategoryServices::getInstance()->getL2ListByIds($categoryIds);
    }

    private function getQueryByGoodsFilter(GoodsListInput $input)
    {
        $query = Goods::query()->where('is_on_sale', 1)->where('deleted', 0);

        if (!empty($input->brandId)) {
            $query->where('brand_id', $input->brandId);
        }
        if (!is_null($input->isNew)) {
            $query->where('is_new', $input->isNew);
        }
        if (!is_null($input->isHot)) {
            $query->where('is_hot', $input->isHot);
        }
        if (!empty($input->keyword)) {
            $query->where(function (Builder $query) use ($input) {
                $query->where('keywords', 'like', "%$input->keyword%")->orWhere('name', 'like', "%$input->keyword%");
            });
        }

        return $query;
    }
}
