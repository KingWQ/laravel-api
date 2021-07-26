<?php

namespace App\Models;


class Comment extends BaseModel
{
    protected $table = 'comment';

    protected $fillable = [];

    protected $hidden = [];

    protected $casts = [
        'deleted'       => 'boolean',
        'picUrls'      => 'array',
    ];

}
