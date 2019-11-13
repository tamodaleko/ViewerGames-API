<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class MatchResource extends Resource
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
            'queue_id' => $this->queue_id,
            'mvp' => $this->mvp ? $this->mvpUser->id : null,
            'mvp_name' => $this->mvp ? $this->mvpUser->name : null,
            'mvp_username' => $this->mvp ? $this->mvpUser->username : null,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
            'completed_at' => $this->completed_at ? $this->completed_at->format('Y-m-d H:i:s') : null
        ];
    }
}
