<?php

namespace App\Models;


class Collect extends BaseModel
{
    protected $table = 'collect';

    protected $fillable = [];

    protected $hidden = [];

    protected $casts = [
        'deleted'       => 'boolean',
    ];

}
