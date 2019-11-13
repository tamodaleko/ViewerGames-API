<?php

namespace App\Models\Queue;

use Illuminate\Database\Eloquent\Model;

class QueueStatus extends Model
{
    const CLOSED = 1;
    const OPEN = 2;
    const PAUSED= 3;
    const IN_PROGRESS = 4;

    /**
     * @var bool
     */
    public $timestamps = false;
}
