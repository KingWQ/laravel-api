<?php

namespace App\Models;


class Comment extends BaseModel
{
    protected $casts = [
        'picUrls' => 'array',
    ];

}
