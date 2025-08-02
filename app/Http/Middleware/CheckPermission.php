<?php

namespace App\Http\Middleware;

use App\Http\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!Auth::check()) {
            return ApiResponse::error('Authentication required', 401);
        }

        $user = Auth::user();

        if (!$user->hasPermission($permission)) {
            return ApiResponse::error('Access denied. Insufficient permissions.', 403);
        }

        return $next($request);
    }
}
