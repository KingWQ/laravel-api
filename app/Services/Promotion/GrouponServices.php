<?php

namespace App\Services\Promotion;

use App\Enums\GrouponEnums;
use App\Inputs\PageInput;
use App\Models\Promotion\GrouponRules;
use App\Services\BaseServices;

class GrouponServices extends BaseServices
{


    public function getGrouponRules(PageInput $page, $columns = ['*'])
    {
        return GrouponRules::query()
            ->where('status', GrouponEnums::RULE_STATUS_ON)
            ->orderBy($page->sort, $page->order)
            ->paginate($page->limit, $columns, 'page', $page->page);
    }

}
