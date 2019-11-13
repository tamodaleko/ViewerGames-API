<?php

namespace App\Http\Controllers;

use App\Http\Resources\MatchPrivateResourceCollection;
use App\Http\Resources\MatchPublicResourceCollection;
use App\Http\Resources\UserResourceCollection;
use App\Models\Match;
use App\Models\User\User;
use Illuminate\Http\Request;

class MatchController extends Controller
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
                'mvpsForUnAuthenticated',
                'completedForUnAuthenticated',
                'teamsForUnAuthenticated'
            ]
        ]);
    }

    /**
     * Show user's mvps.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\UserResourceCollection
     */
    public function mvps(Request $request)
    {
        $days = (isset($request->days) && is_numeric($request->days)) ? $request->days : 30;
        
        return $this->wrapResponse(new UserResourceCollection($request->user()->getUserMvps($days)));
    }

    /**
     * Show user's completed matches.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\MatchPrivateResourceCollection
     */
    public function completed(Request $request)
    {
        return $this->wrapResponse(new MatchPrivateResourceCollection($request->user()->getCompletedMatches()));
    }

    /**
     * Show teams for user's selected match.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Match $match
     * @return array
     */
    public function teams(Request $request, Match $match)
    {
        return $this->wrapResponse($match->getTeamsAndPlayers(false));
    }

    /**
     * Show mvps for specified unauthenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User\User $user
     * @return \App\Http\Resources\UserResourceCollection
     */
    public function mvpsForUnAuthenticated(Request $request, User $user)
    {
        $days = (isset($request->days) && is_numeric($request->days)) ? $request->days : 30;

        return $this->wrapResponse(new UserResourceCollection($user->getUserMvps($days)));
    }

    /**
     * Show completed matches for specified unauthenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User\User $user
     * @return \App\Http\Resources\MatchPublicResourceCollection
     */
    public function completedForUnAuthenticated(Request $request, User $user)
    {
        $paginate = true;

        if (isset($request->paginate)) {
            $paginate = $request->paginate ? true : false;
        }

        $per_page = $request->per_page ?: 5;

        return $this->wrapResponse(new MatchPublicResourceCollection($user->getCompletedMatches($paginate, $per_page)));
    }

    /**
     * Show teams for specified unauthenticated user's match.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User\User $user
     * @param \App\Models\Match $match
     * @return array
     */
    public function teamsForUnAuthenticated(Request $request, User $user, Match $match)
    {
        return $this->wrapResponse($match->getTeamsAndPlayers(false));
    }
}
