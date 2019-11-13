<?php

namespace App\Http\Controllers;

use App\Http\Resources\SocialResource;
use App\Models\Social;
use App\Models\User\User;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => ['showForUnAuthenticated']
        ]);
    }

    /**
     * Show user's social accounts.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\SocialResource
     */
    public function show(Request $request)
    {
        if (!$request->user()->social) {
            return $this->errorResponse('User has no social accounts created.');
        }

        return $this->wrapResponse(new SocialResource($request->user()->social));
    }

    /**
     * Store user's social accounts.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\SocialResource
     */
    public function store(Request $request)
    {
        $request->validate(Social::$rules);

        $social = $request->user()->social ?: new Social;

        $social->user_id = $request->user()->id;
        $social->twitter = $request->twitter;
        $social->facebook = $request->facebook;
        $social->discord = $request->discord;

        if (!$social->save()) {
            return $this->errorResponse('User\'s social accounts could not be updated.', 500);
        }

        return $this->wrapResponse(new SocialResource($social));
    }

    /**
     * Show social accounts for specified unauthenticated user.
     *
     * @param \App\Models\User\User $user
     * @return \App\Http\Resources\SocialResource
     */
    public function showForUnAuthenticated(User $user)
    {
        if (!$user->social) {
            return $this->errorResponse('User has no social accounts created.');
        }

        return $this->wrapResponse(new SocialResource($user->social));
    }
}
