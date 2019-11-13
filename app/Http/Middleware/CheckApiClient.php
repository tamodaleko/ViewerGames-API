<?php

namespace App\Http\Middleware;

use App\Models\OauthClient;
use Closure;
use Illuminate\Auth\AuthenticationException;

class CheckApiClient
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $clientId = $request->header('Client-ID');

        if (!$clientId) {
            throw new AuthenticationException;
        }

        $client = OauthClient::where('secret', $clientId)->first();

        if (!$client) {
            throw new AuthenticationException;
        }

        return $next($request);
    }
}
