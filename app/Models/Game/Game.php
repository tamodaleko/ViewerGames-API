<?php

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the servers for the game.
     */
    public function servers()
    {
        return $this->hasMany('App\Models\Game\GameServer');
    }

    /**
     * Get the maps for the game.
     */
    public function maps()
    {
        return $this->hasMany('App\Models\Game\GameMap');
    }

    /**
     * Get the ranks for the game.
     */
    public function ranks()
    {
        return $this->hasMany('App\Models\Game\GameRank');
    }

    /**
     * Get the roles for the game.
     */
    public function roles()
    {
        return $this->hasMany('App\Models\Game\GameRole');
    }

    /**
     * Get the game settings record associated with the game.
     */
    public function settings()
    {
        return $this->hasOne('App\Models\Game\GameSetting');
    }
}
