<?php

namespace App\Http\Controllers;

use App\Http\Resources\BadgeResourceCollection;
use App\Models\User\User;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => ['allForUnAuthenticated']
        ]);
    }

    /**
     * Show user's badges.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\BadgeResourceCollection
     */
    public function all(Request $request)
    {
        if (!$request->user()->badges->count()) {
            return $this->errorResponse('User has no badges granted.');
        }

        return $this->wrapResponse(new BadgeResourceCollection($request->user()->badges));
    }

    /**
     * Show badges for specified unauthenticated user.
     *
     * @param \App\Models\User\User $user
     * @return \App\Http\Resources\BadgeResourceCollection
     */
    public function allForUnAuthenticated(User $user)
    {
        if (!$user->badges->count()) {
            return $this->errorResponse('User has no badges granted.');
        }

        return $this->wrapResponse(new BadgeResourceCollection($user->badges));
    }
}
