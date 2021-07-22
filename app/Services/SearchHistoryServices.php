<?php

namespace App\Services;

use App\Models\SearchHistory;

class SearchHistoryServices extends BaseServices
{
    //获取在售商品的数量
    public function save($userId, $keyword, $from)
    {
        $history = new SearchHistory();
        $history->fill([
            'user_id' => $userId,
            'keyword' => $keyword,
            'from'    => $from,
        ]);
        $history->save();

        return $history;
    }

}
