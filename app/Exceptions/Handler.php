<?php

namespace App\Exceptions;

use App\Http\Helpers\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Central API Error Handler
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->handleApiException($e, $request);
            }
        });
    }

    /**
     * Handle API exceptions and return consistent JSON responses
     */
    private function handleApiException(Throwable $e, Request $request)
    {
        // 404 - Route not found
        if ($e instanceof NotFoundHttpException) {
            return ApiResponse::error('Route not found', 404);
        }

        // 404 - Model not found
        if ($e instanceof ModelNotFoundException) {
            return ApiResponse::error('Resource not found', 404);
        }

        // 405 - Method not allowed
        if ($e instanceof MethodNotAllowedHttpException) {
            return ApiResponse::error('Method not allowed', 405);
        }

        // 401 - Authentication required
        if ($e instanceof AuthenticationException) {
            return ApiResponse::error('Authentication required', 401);
        }

        // 401 - Unauthorized
        if ($e instanceof UnauthorizedHttpException) {
            return ApiResponse::error('Unauthorized', 401);
        }

        // 403 - Access denied
        if ($e instanceof AccessDeniedHttpException) {
            return ApiResponse::error('Access denied', 403);
        }

        // 422 - Validation failed
        if ($e instanceof ValidationException) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }

        // 500 - Database errors
        if ($e instanceof QueryException) {
            $message = config('app.debug') ? $e->getMessage() : 'Database error occurred';
            return ApiResponse::error($message, 500);
        }

        // 500 - General exceptions
        $message = config('app.debug') ? $e->getMessage() : 'Internal server error';
        return ApiResponse::error($message, 500);
    }
}
