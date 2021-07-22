<?php

namespace App\Services\Goods;

use App\Models\Goods\Category;
use App\Services\BaseServices;

class CategoryServices extends BaseServices
{
    public function getL1List($columns=['*'])
    {
        return Category::query()->where('pid', 0)->where('level', 'L1')->get($columns);
    }

    public function getL2ListDataByPid($pid,$columns=['*'])
    {
        return Category::query()->where('pid', $pid)->where('level', 'L2')->get($columns);
    }
}
