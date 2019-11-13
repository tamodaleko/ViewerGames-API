<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserSchedule extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'day' => 'required|integer',
        'start_time' => 'required|string|max:10'
    ];
}
