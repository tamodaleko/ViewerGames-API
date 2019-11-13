<?php

namespace App\Http\Resources\Game;

use Illuminate\Http\Resources\Json\Resource;

class GameSettingResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'streamer_modes' => $this->streamer_modes,
            'teams' => $this->teams,
            'players_per_team' => $this->players_per_team,
            'start_timers' => $this->start_timers,
            'ticket_costs' => $this->ticket_costs
        ];
    }
}
