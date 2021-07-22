<?php

namespace App\Services\Goods;

use App\Models\Goods\Category;
use App\Services\BaseServices;

class CategoryServices extends BaseServices
{
    public function getL1List($columns=['*'])
    {
        return Category::query()->where('pid', 0)->where('level', 'L1')
            ->where('deleted',0)->get($columns);
    }

    public function getL1ById($id,$columns=['*'])
    {
        return Category::query()->where('id', $id)->where('level', 'L1')
            ->where('deleted',0)->get($columns);

    }

    public function getL2ListByPid($pid,$columns=['*'])
    {
        return Category::query()->where('pid', $pid)->where('level', 'L2')
            ->where('deleted',0)->get($columns);
    }

    public function getCategory($id)
    {
        return Category::query()->find($id);
    }

    public function getL2ListByIds(array $ids)
    {
        if(empty($ids)){
            return collect([]);
        }
        return Category::query()->whereIn('id', $ids)->get();
    }

}
