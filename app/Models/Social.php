<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Social extends Model
{
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'twitter' => 'nullable|string|max:100',
        'facebook' => 'nullable|string|max:100',
        'discord' => 'nullable|string|max:255'
    ];
}
