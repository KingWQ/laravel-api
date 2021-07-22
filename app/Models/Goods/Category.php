<?php

namespace App\Models\Goods;

use App\Models\BaseModel;

class Category extends BaseModel
{
    protected $table = 'category';
    public const CREATED_AT = 'add_time';
    public const UPDATED_AT = 'update_time';

    protected $fillable = [];

    protected $hidden = [];

    protected $casts = [
        'deleted'=>'boolean'
    ];

}
