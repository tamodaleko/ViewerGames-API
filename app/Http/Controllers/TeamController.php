<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
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
     * Mark team as winner.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Team $team
     * @return \Illuminate\Http\Response
     */
    public function winner(Request $request, Team $team)
    {
        if (!$team->winner()) {
            return $this->errorResponse('Team could not be marked as winner.');
        }

        return $this->successResponse('Team has been marked as winner successfully.');
    }
}
