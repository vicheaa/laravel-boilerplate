<?php

namespace App\Http\Middleware;

use App\Http\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return ApiResponse::error('Authentication required', 401);
        }

        $user = Auth::user();

        if (!$user->hasRole($role)) {
            return ApiResponse::error('Access denied. Insufficient role.', 403);
        }

        return $next($request);
    }
}
