<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth', 'prefix' => '/auth'], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::get('/users', [AuthController::class, 'getAllUsers']);
    Route::get('/permissions', [AuthController::class, 'getUserPermissions']);

    // User role management
    Route::put('/users/{user}/role', [AuthController::class, 'updateUserRole']);
    Route::delete('/users/{user}/role', [AuthController::class, 'removeUserRole']);
});
Route::post('/auth/create-user', [AuthController::class, 'createUser']);
Route::post('/auth/register', [AuthController::class, 'signup']);
Route::post('/auth/login', [AuthController::class, 'login']);
