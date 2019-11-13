<?php

namespace App\Http\Controllers;

use App\Http\Resources\Game\GameResourceCollection;
use App\Http\Resources\Game\GameMapResourceCollection;
use App\Http\Resources\Game\GameRankResourceCollection;
use App\Http\Resources\Game\GameRoleResourceCollection;
use App\Http\Resources\Game\GameServerResourceCollection;
use App\Http\Resources\Game\GameSettingResource;
use App\Models\Game\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => [
                'allForUnAuthenticated',
                'infoForUnAuthenticated'
            ]
        ]);
    }

    /**
     * Get all games.
     *
     * @return \App\Http\Resources\Game\GameResourceCollection
     */
    public function allForUnAuthenticated()
    {
        return $this->wrapResponse(new GameResourceCollection(Game::all()));
    }

    /**
     * Show game info.
     *
     * @param \App\Models\Game\Game $game
     * @return array
     */
    public function infoForUnAuthenticated(Game $game)
    {
        return $this->wrapResponse([
            'servers' => new GameServerResourceCollection($game->servers),
            'maps' => new GameMapResourceCollection($game->maps),
            'ranks' => new GameRankResourceCollection($game->ranks),
            'roles' => new GameRoleResourceCollection($game->roles),
            'settings' => new GameSettingResource($game->settings)
        ]);
    }
}
