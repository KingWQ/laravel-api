<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class BaseModel extends Model
{
    use BooleanSoftDeletes;

    public const CREATED_AT = 'add_time';
    public const UPDATED_AT = 'update_time';
    public $defaultCasts = ['deleted' => 'boolean'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        //默认数据转换
        parent::mergeCasts($this->defaultCasts);
    }

    //表名驼峰去掉复数
    public function getTable()
    {
        return $this->table ?? Str::snake(class_basename($this));
    }

    public static function new()
    {
        //如果是new self则new出来是BaseModel对象 不是模型了
        // $couponUser = new CouponUser()
        // $couponUser = CouponUser::new()
        return new static();
    }


    //字段驼峰写法
    public function toArray()
    {
        $items = parent::toArray();
//        $items = array_filter($items, function ($item){
//            return !is_null($item);
//        });

        $keys   = array_keys($items);
        $keys   = array_map(function ($item) {
            return lcfirst(Str::studly($item));
        }, $keys);
        $values = array_values($items);

        return array_combine($keys, $values);
    }


    //在toArray的时候调用
    public function serializeDate(DateTimeInterface $date)
    {
        return Carbon::instance($date)->toDateTimeString();
    }


}
