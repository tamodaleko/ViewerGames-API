<?php

namespace App\Http\Resources\Game;

use Illuminate\Http\Resources\Json\Resource;

class GameRoleResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
