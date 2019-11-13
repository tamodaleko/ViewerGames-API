<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserChannelSubscription extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'channel_user_id'];
}
