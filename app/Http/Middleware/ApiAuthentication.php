<?php

namespace App\Http\Middleware;

use App\Http\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class ApiAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the request has a Bearer token
        if (!$request->bearerToken()) {
            return ApiResponse::error('No token provided', 401);
        }

        // Try to authenticate using Sanctum
        try {
            $token = PersonalAccessToken::findToken($request->bearerToken());

            if (!$token) {
                return ApiResponse::error('Invalid token', 401);
            }

            $user = $token->tokenable;

            if (!$user) {
                return ApiResponse::error('Token not associated with any user', 401);
            }

            // Set the authenticated user
            Auth::setUser($user);
        } catch (\Exception $e) {
            return ApiResponse::error('Token validation failed', 401);
        }

        return $next($request);
    }
}
