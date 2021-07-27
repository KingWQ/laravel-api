<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Constant;
use App\Inputs\GoodsListInput;
use App\Services\CollectServices;
use App\Services\CommentServices;
use App\Services\Goods\BrandServices;
use App\Services\Goods\CategoryServices;
use App\Services\Goods\GoodsServices;
use App\Services\SearchHistoryServices;
use Illuminate\Http\Request;

class GoodsController extends WxController
{
    protected $only = [];

    //商品列表
    public function list()
    {
        $input = GoodsListInput::new();


        if ($this->isLogin() && !empty($keyword)) {
            SearchHistoryServices::getInstance()->save($this->userId(), $keyword, Constant::SEARCH_HISTORY_FROM_WX);
        }

        $columns      = ['id', 'name', 'brief', 'pic_url', 'is_new', 'is_hot', 'counter_price', 'retail_price'];
        $goodsList    = GoodsServices::getInstance()->listGoods($input, $columns);
        $categoryList = GoodsServices::getInstance()->list2Category($input);

        $goodsList                       = $this->paginate($goodsList);
        $goodsList['filterCategoryList'] = $categoryList;

        return $this->success($goodsList);
    }

    //商品详情
    public function detail(Request $request)
    {
        $id = $this->verifyId('id', 0);
        if (empty($id)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }
        $info = GoodsServices::getInstance()->getGoods($id);
        if (empty($info)) {
            return $this->fail(CodeResponse::PARAM_NOT_EMPTY);
        }

        $attr    = GoodsServices::getInstance()->getGoodsAttribute($id);
        $spec    = GoodsServices::getInstance()->getGoodsSpecification($id);
        $product = GoodsServices::getInstance()->getGoodsProduct($id);
        $issue   = GoodsServices::getInstance()->getGoodsIssue();
        $brand   = $info->brand_id ? BrandServices::getInstance()->getBrand($info->brand_id) : (object)[];
        $comment = CommentServices::getInstance()->getCommentWithUserInfo($id);

        $userHasCollect = 0;
        if ($this->isLogin()) {
            //用户是否收藏该商品
            $userHasCollect = CollectServices::getInstance()->countByGoodsId($this->userId(), $id);

            //记录用户的足迹，异步处理
            GoodsServices::getInstance()->saveFootprint($this->userId(), $id);
        }
        //todo 团购信息
        //todo 系统配置

        return $this->success([
            'info'              => $info,
            'userHasCollect'    => $userHasCollect,
            'issue'             => $issue,
            'comment'           => $comment,
            'specificationList' => $spec,
            'productList'       => $product,
            'attribute'         => $attr,
            'brand'             => $brand,
            'groupon'           => [],
            'share'             => false,
            'shareImage'        => $info->share_url,
        ]);
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
        $id = $this->verifyId('id', 0);
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
