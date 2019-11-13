<?php

namespace App\Http\Resources\Player;

use Illuminate\Http\Resources\Json\Resource;

class PlayerCardResource extends Resource
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
            'game_rank_id' => $this->game_rank_id,
            'game_rank' => $this->gameRank ? $this->gameRank->name : null,
            'game_role_id' => $this->game_role_id,
            'game_role' => $this->gameRole ? $this->gameRole->name : null,
            'in_game_name' => $this->in_game_name,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null
        ];
    }
}
