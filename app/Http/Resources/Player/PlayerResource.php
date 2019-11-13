<?php

namespace App\Http\Resources\Player;

use Illuminate\Http\Resources\Json\Resource;

class PlayerResource extends Resource
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
            'player_id' => $this->id,
            'user_id' => $this->user_id,
            'username' => $this->user ? $this->user->username : null,
            'name' => $this->user ? $this->user->name : null,
            'avatar' => $this->user ? $this->user->avatar : null,
            'match_id' => $this->match_id,
            'team_id' => $this->team_id,
            'game_role_id' => $this->game_role_id,
            'game_role' => $this->gameRole ? $this->gameRole->name : null,
            'in_game_name' => $this->in_game_name,
            'status' => $this->status,
            'ready' => $this->ready,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null
        ];
    }
}
