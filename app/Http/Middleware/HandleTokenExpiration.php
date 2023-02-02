<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HandleTokenExpiration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $accessToken = session('access_token');

        if (!$accessToken) {
            $accessToken = $this->fetchAccessToken();
            session(['access_token' => $accessToken]);
        }

        $response = $next($request);

        if ($response->getStatusCode() == 401) {
            $accessToken = $this->fetchAccessToken();
            session(['access_token' => $accessToken]);

            $response = $next($request);
        }

        return $next($request);
        return $response;
    }

    public function fetchAccessToken(): string
    {
        $response = Http::accept('application/json')
            ->post(config('emkl.api.url') . '/oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => config('emkl.api.client_id'),
                'client_secret' => config('emkl.api.client_secret'),
                'scope' => null,
            ]);

        if ($response->ok()) {
            return $response->json('access_token');
        } else {
            throw new Exception("Error while fetching new access token.");
        }
    }
}
