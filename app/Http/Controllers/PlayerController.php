<?php

namespace App\Http\Controllers;

use App\Http\Resources\Player\PlayerResource;
use App\Models\Badge;
use App\Models\Player\Player;
use App\Models\User\UserBadge;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Remove player.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Player\Player $player
     * @return \App\Http\Resources\Player\PlayerResource
     */
    public function remove(Request $request, Player $player)
    {
        $guest = $player->remove();

        if ($guest === false) {
            return $this->errorResponse('Player could not be removed.');
        }

        return $this->wrapResponse(new PlayerResource($guest));
    }

    /**
     * Replace player.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Player\Player $player
     * @return \App\Http\Resources\Player\PlayerResource
     */
    public function replace(Request $request, Player $player)
    {
        $newPlayer = $player->replace();

        if ($newPlayer === false) {
            return $this->errorResponse('Player could not be replaced.');
        }

        return $this->wrapResponse(new PlayerResource($newPlayer));
    }

    /**
     * Mark player as MVP.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Player\Player $player
     * @return \Illuminate\Http\Response
     */
    public function mvp(Request $request, Player $player)
    {
        if (!$player->mvp()) {
            return $this->errorResponse('Player could not be marked as MVP.');
        }

        UserBadge::grantBadge(Badge::ID_MVP, $player->user_id, $request->user()->id, $player->match_id);

        return $this->successResponse('Player has been marked as MVP successfully.');
    }
}
