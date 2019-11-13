<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['match_id', 'winner_status'];

    /**
     * Get the match record associated with the team.
     */
    public function match()
    {
        return $this->belongsTo('App\Models\Match');
    }

    /**
     * Mark team as winner.
     *
     * @return bool
     */
    public function winner()
    {
        if ($this->match->completed_at) {
            return false;
        }

        foreach ($this->match->teams as $team) {
            $team->winner_status = false;
            $team->save();
        }

        $this->winner_status = true;

        return $this->save();
    }
}
