<?php

namespace App\Http\Resources;

use App\Http\Resources\QueueResource;
use Illuminate\Http\Resources\Json\Resource;

class MatchPrivateResource extends Resource
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
            'queue' => QueueResource::make($this->queue),
            'mvp' => $this->mvp ? $this->mvpUser->id : null,
            'mvp_name' => $this->mvp ? $this->mvpUser->name : null,
            'mvp_username' => $this->mvp ? $this->mvpUser->username : null,
            'mvp_avatar' => $this->mvp ? $this->mvpUser->avatar : null,
            'total_players' => $this->getPlayerCount('total'),
            'ticket_players' => $this->getPlayerCount('tickets'),
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
            'completed_at' => $this->completed_at ? $this->completed_at->format('Y-m-d H:i:s') : null
        ];
    }
}
