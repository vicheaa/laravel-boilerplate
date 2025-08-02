<?php

use App\Http\Controllers\PermissionController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth', 'prefix' => '/permissions'], function () {
    // Get all permissions
    Route::get('/', [PermissionController::class, 'index']);

    // Get specific permission
    Route::get('/{permission}', [PermissionController::class, 'show']);

    // Create new permission
    Route::post('/', [PermissionController::class, 'store']);

    // Update permission
    Route::put('/{permission}', [PermissionController::class, 'update']);

    // Delete permission
    Route::delete('/{permission}', [PermissionController::class, 'destroy']);

    // Get permission hierarchy
    Route::get('/hierarchy', [PermissionController::class, 'hierarchy']);

    // Get permissions by action
    Route::get('/by-action', [PermissionController::class, 'byAction']);

    // Get permissions by subject
    Route::get('/by-subject', [PermissionController::class, 'bySubject']);
});
