<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    const ID_STREAMER = 1;
    const ID_MVP = 2;
    const ID_SUPPORT = 3;

    /**
     * @var bool
     */
    public $timestamps = false;
}
