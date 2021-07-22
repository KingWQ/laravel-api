<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BaseModel extends Model
{
    //字段驼峰写法
    public function toArray()
    {
        $items = parent::toArray();
        $items = array_filter($items, function ($item){
            return !is_null($item);
        });

        $keys = array_keys($items);
        $keys = array_map(function ($item){
            return lcfirst(Str::studly($item));
        },$keys);
        $values = array_values($items);
        return array_combine($keys,$values);
    }

}
