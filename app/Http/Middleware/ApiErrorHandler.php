<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiErrorHandler
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
        try {
            $response = $next($request);
            return $response;
        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Route not found',
            ], 404);
        } catch (MethodNotAllowedHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed',
            ], 405);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            $message = config('app.debug') ? $e->getMessage() : 'Internal server error';
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 500);
        }
    }
}
