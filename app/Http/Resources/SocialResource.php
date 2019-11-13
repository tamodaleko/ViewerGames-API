<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class SocialResource extends Resource
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
            'user_id' => $this->user_id,
            'twitter' => $this->twitter,
            'discord' => $this->discord,
            'facebook' => $this->facebook
        ];
    }
}
