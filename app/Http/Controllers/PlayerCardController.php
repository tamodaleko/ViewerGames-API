<?php

namespace App\Http\Controllers;

use App\Http\Resources\Player\PlayerCardResource;
use App\Http\Resources\Player\PlayerCardResourceCollection;
use App\Models\Notification\Notification;
use App\Models\Player\PlayerCard;
use App\Models\User\User;
use Illuminate\Http\Request;

class PlayerCardController extends Controller
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
                'showForUnAuthenticated'
            ]
        ]);
    }

    /**
     * Show user's player cards.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\Player\PlayerCardResourceCollection
     */
    public function all(Request $request)
    {
        $cards = $request->user()->playerCards;

        if (!count($cards)) {
            return $this->errorResponse('User has no player cards created.');
        }

        return $this->wrapResponse(new PlayerCardResourceCollection($cards));
    }

    /**
     * Show user's player card by id.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Player\PlayerCard $card
     * @return \App\Http\Resources\Player\PlayerCardResource
     */
    public function show(Request $request, PlayerCard $card)
    {
        if ($request->user()->id !== $card->user_id) {
            return $this->errorResponse('Access denied.', 401);
        }

        return $this->wrapResponse(new PlayerCardResource($card));
    }

    /**
     * Create new user's player card.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\Player\PlayerCardResource
     */
    public function store(Request $request)
    {
        $validated = $request->validate(PlayerCard::$rules);
        $validated['user_id'] = $request->user()->id;

        if (PlayerCard::checkGameServerCombination($validated['user_id'], $validated['game_id'], $validated['game_server_id'])) {
            return $this->errorResponse('Combination of game and server is already in use.', 500);
        }

        $card = PlayerCard::create($validated);

        if (!$card) {
            return $this->errorResponse('Player card could not be created.', 500);
        }

        Notification::toggle($request->user()->id, 'no_player_card', true);

        return $this->wrapResponse(new PlayerCardResource(PlayerCard::find($card->id)));
    }

    /**
     * Update user's player card.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Player\PlayerCard $card
     * @return \App\Http\Resources\Player\PlayerCardResource
     */
    public function update(Request $request, PlayerCard $card)
    {
        $validated = $request->validate(PlayerCard::$rules);
        $validated['user_id'] = $request->user()->id;

        $card->fill($validated);

        if ($card->game_id != $card->getOriginal('game_id') || $card->game_server_id != $card->getOriginal('game_server_id')) {
            if (PlayerCard::checkGameServerCombination($card->user_id, $card->game_id, $card->game_server_id)) {
                return $this->errorResponse('Combination of game and server is already in use.', 500);
            }
        }

        if (!$card->save()) {
            return $this->errorResponse('Player card could not be updated.', 500);
        }

        return $this->wrapResponse(new PlayerCardResource(PlayerCard::find($card->id)));
    }

    /**
     * Delete user's player card.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Player\PlayerCard $card
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, PlayerCard $card)
    {
        if ($request->user()->id !== $card->user_id) {
            return $this->errorResponse('Access denied.', 401);
        }

        if (!$card->delete()) {
            return $this->errorResponse('Player card could not be deleted.', 500);
        }

        if (!$request->user()->playerCards()->count()) {
            Notification::toggle($request->user()->id, 'no_player_card', false);
        }

        return $this->successResponse('Player card has been deleted successfully.');
    }

    /**
     * Show player cards for specified unauthenticated user.
     *
     * @param \App\Models\User\User $user
     * @return \App\Http\Resources\Player\PlayerCardResourceCollection
     */
    public function allForUnAuthenticated(User $user)
    {
        $cards = $user->playerCards;

        if (!count($cards)) {
            return $this->errorResponse('User has no player cards created.');
        }

        return $this->wrapResponse(new PlayerCardResourceCollection($cards));
    }

    /**
     * Show specified player card for unauthenticated user.
     *
     * @param \App\Models\Player\PlayerCard $card
     * @return \App\Http\Resources\Player\PlayerCardResource
     */
    public function showForUnAuthenticated(PlayerCard $card)
    {
        return $this->wrapResponse(new PlayerCardResource($card));
    }
}
