<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'key' => 'required|string|max:255',
        'value' => 'required|string|max:255'
    ];
}
