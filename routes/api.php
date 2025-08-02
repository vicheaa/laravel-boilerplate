<?php

use Illuminate\Support\Facades\Route;

require __DIR__ . '/api/auth.php';

// Handle 404 for API routes
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Route not found',
    ], 404);
});
