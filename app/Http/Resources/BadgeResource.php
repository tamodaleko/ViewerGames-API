<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class BadgeResource extends Resource
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
            'receiver_user_id' => $this->receiver_user_id,
            'issuer_user_id' => $this->issuer_user_id,
            'issuer_name' => $this->issuerUser ? $this->issuerUser->name : null,
            'issuer_username' => $this->issuerUser ? $this->issuerUser->username : null,
            'badge_id' => $this->badge_id,
            'badge_name' => $this->badge ? $this->badge->title : null,
            'match_id' => $this->match_id,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null
        ];
    }
}
