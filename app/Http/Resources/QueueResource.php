<?php

namespace App\Http\Resources;

use App\Models\Queue\Queue;
use Illuminate\Http\Resources\Json\Resource;

class QueueResource extends Resource
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
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => $this->user ? $this->user->name : null,
            'game_id' => $this->game_id,
            'game' => $this->game ? $this->game->name : null,
            'game_server_id' => $this->game_server_id,
            'game_server' => $this->gameServer ? $this->gameServer->name : null,
            'game_map_id' => $this->game_map_id,
            'game_map' => $this->gameMap ? $this->gameMap->name : null,
            'game_rank_id' => $this->game_rank_id,
            'game_rank' => $this->gameRank ? $this->gameRank->name : null,
            'queue_status_id' => $this->queue_status_id,
            'queue_status' => $this->status ? $this->status->name : null,
            'role_enforcement' => $this->role_enforcement,
            'streamer_mode' => $this->streamer_mode,
            'streamer_mode_name' => Queue::$streamerModes[$this->streamer_mode],
            'active' => $this->active,
            'team_count' => $this->team_count,
            'players_per_team' => $this->players_per_team,
            'min_ticket_cost' => $this->min_ticket_cost,
            'countdown_timer' => $this->countdown_timer,
            'countdown_progress' => $this->countdown_progress,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null
        ];
    }
}
