<?php

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Model;

class GameSetting extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'streamer_modes' => 'array',
        'teams' => 'array',
        'players_per_team' => 'array',
        'start_timers' => 'array',
        'ticket_costs' => 'array'
    ];
}
