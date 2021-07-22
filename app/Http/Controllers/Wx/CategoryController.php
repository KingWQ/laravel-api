<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Services\Goods\CategoryServices;
use Illuminate\Http\Request;

class CategoryController extends WxController
{
    protected $only = [];

    public function index(Request $request)
    {
        $id = $request->input('id',0);
        $categoryList = CategoryServices::getInstance()->getL1List();

        if(empty($id)){
            $currentCategory = $categoryList->first();
        }else{
            $currentCategory = $categoryList->where('id', $id)->first();
        }

        $currentSubCategory = null;
        if(!$currentCategory){
            $currentSubCategory = CategoryServices::getInstance()->getL2ListByPid($currentCategory->id);
        }

        return $this->success(compact('currentCategory', 'categoryList', 'currentSubCategory'));
    }

    public function current(Request $request)
    {
        $id = $request->input('id',0);
        if(empty($id)){
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }
        $categoryList = CategoryServices::getInstance()->getL1List();
        $currentCategory = $categoryList->where('id', $id)->first();

        if (empty($currentCategory)) {
            return $this->fail(CodeResponse::PARAM_NOT_EMPTY, '参数值不对');
        }

        $currentSubCategory = CategoryServices::getInstance()->getL2ListByPid($currentCategory->id);

        return $this->success(compact('currentCategory',  'currentSubCategory'));

    }


}
