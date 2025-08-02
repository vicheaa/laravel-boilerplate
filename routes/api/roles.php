<?php

use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth', 'prefix' => '/roles'], function () {
    // Get all roles
    Route::get('/', [RoleController::class, 'index']);

    // Get specific role
    Route::get('/{role}', [RoleController::class, 'show']);

    // Create new role
    Route::post('/', [RoleController::class, 'store']);

    // Update role
    Route::put('/{role}', [RoleController::class, 'update']);

    // Delete role
    Route::delete('/{role}', [RoleController::class, 'destroy']);

    // Get role permissions
    Route::get('/{role}/permissions', [RoleController::class, 'permissions']);

    // Assign permissions to role
    Route::post('/{role}/permissions', [RoleController::class, 'assignPermissions']);

    // Remove permissions from role
    Route::delete('/{role}/permissions', [RoleController::class, 'removePermissions']);
});
