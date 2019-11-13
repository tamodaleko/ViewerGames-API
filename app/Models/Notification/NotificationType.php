<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Model;

class NotificationType extends Model
{
    const TYPE_REQUIRED = 1;
    const TYPE_INFORMATIONAL = 2;

    /**
     * @var bool
     */
    public $timestamps = false;
}
