<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User\User;
use Illuminate\Http\Request;

class UserController extends Controller
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
                'showForUnAuthenticated',
                'showByUsernameForUnAuthenticated'
            ]
        ]);
    }

    /**
     * Display currently authenticated user's profile.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\UserResource
     */
    public function show(Request $request)
    {
        return $this->wrapResponse(new UserResource($request->user()));
    }

    /**
     * Display the user resource by id.
     *
     * @param int $id
     * @return \App\Http\Resources\UserResource
     */
    public function showForUnAuthenticated($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('User not found.');
        }

        return $this->wrapResponse(new UserResource($user));
    }

    /**
     * Display the user resource by username.
     *
     * @param string $username
     * @return \App\Http\Resources\UserResource
     */
    public function showByUsernameForUnAuthenticated($username)
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return $this->errorResponse('User not found.');
        }

        return $this->wrapResponse(new UserResource($user));
    }
}
