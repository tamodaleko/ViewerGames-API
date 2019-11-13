<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class SocialAuthController extends Controller
{
    /**
     * @var Request $request
     */
    public function handle(Request $request)
    {
        $request->request->add([
            'grant_type' => 'social',
            'client_id' => 1,
            'client_secret' => $request->client_secret,
            'provider' => $request->provider,
            'authorization_code' => $request->authorization_code,
            'streamer' => $request->streamer ?: false
        ]);

        $proxy = Request::create('oauth/token', 'POST');

        return Route::dispatch($proxy);
    }
}
