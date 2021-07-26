<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class BaseModel extends Model
{
    public const CREATED_AT = 'add_time';
    public const UPDATED_AT = 'update_time';

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


    //在toArray的时候调用
    public function serializeDate(DateTimeInterface $date)
    {
        return Carbon::instance($date)->toDateTimeString();
    }

}
