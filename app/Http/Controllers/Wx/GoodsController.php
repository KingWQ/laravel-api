<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Constant;
use App\Services\Goods\CategoryServices;
use App\Services\Goods\GoodsServices;
use App\Services\SearchHistoryServices;
use Illuminate\Http\Request;

class GoodsController extends WxController
{
    protected $only = [];

    public function list(Request $request)
    {
        $categoryId = $request->input('categoryId');
        $brandId    = $request->input('brandId');
        $keyword    = $request->input('keyword');
        $isNew      = $request->input('isNew');
        $isHot      = $request->input('isHot');
        $page       = $request->input('page', 1);
        $limit      = $request->input('limit', 10);
        $sort       = $request->input('sort', 'add_time');
        $order      = $request->input('order', 'desc');

        //todo 验证参数

        if ($this->isLogin() && !empty($keyword)) {
            SearchHistoryServices::getInstance()->save($this->userId(), $keyword, Constant::SEARCH_HISTORY_FROM_WX);
        }

        $columns = ['id','name','brief','pic_url','is_new','is_hot','counter_price','retail_price'];
        $goodsList = GoodsServices::getInstance()
            ->listGoods($categoryId, $brandId, $isNew, $isHot, $keyword,$columns, $sort, $order, $page, $limit);

        $categoryList = GoodsServices::getInstance()->list2Category($brandId, $isNew, $isHot, $keyword);

        $goodsList = $this->paginate($goodsList);
        $goodsList['filterCategoryList'] = $categoryList;
        return $this->success($goodsList);
    }

    public function detail(Request $request)
    {
    }


    //获取在售商品的数量
    public function count()
    {
        $count = GoodsServices::getInstance()->countGoodsOnSales();

        return $this->success($count);
    }

    //获取商品分类的数据
    public function category(Request $request)
    {
        $id = $request->input('id', 0);
        if (empty($id)) {
            return $this->fail(CodeResponse::PARAM_NOT_EMPTY);
        }
        $cur = CategoryServices::getInstance()->getCategory($id);
        if (empty($cur)) {
            return $this->fail(CodeResponse::PARAM_NOT_EMPTY);
        }

        $parent = $children = null;
        if ($cur->pid == 0) {
            $parent   = $cur;
            $children = CategoryServices::getInstance()->getL2ListByPid($cur->id);
            $cur      = $children->first() ?? $cur;
        } else {
            $parent   = CategoryServices::getInstance()->getL1ById($cur->pid);
            $children = CategoryServices::getInstance()->getL2ListByPid($cur->pid);
        }

        return $this->success([
            'currentCategory' => $cur,
            'parentCategory'  => $parent,
            'brotherCategory' => $children,
        ]);
    }
}
