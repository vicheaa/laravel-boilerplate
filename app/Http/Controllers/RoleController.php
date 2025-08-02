<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ApiResponse;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    /**
     * Get all roles with their permissions.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $roles = Role::with('permissions')->get();
            return ApiResponse::success($roles);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve roles: ' . $e->getMessage());
            return ApiResponse::error('Failed to retrieve roles');
        }
    }

    /**
     * Get a specific role with its permissions.
     */
    public function show(Role $role): JsonResponse
    {
        try {
            $role->load('permissions');
            return ApiResponse::success($role);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve role: ' . $e->getMessage());
            return ApiResponse::error('Failed to retrieve role');
        }
    }

    /**
     * Create a new role.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:121|unique:roles,name',
                'display_name' => 'required|string|max:121',
                'display_name_kh' => 'required|string|max:121',
                'restricted' => 'boolean',
                'permission_ids' => 'array',
                'permission_ids.*' => 'exists:permissions,id',
            ]);

            $role = DB::transaction(function () use ($validated) {
                $role = Role::create([
                    'name' => $validated['name'],
                    'display_name' => $validated['display_name'],
                    'display_name_kh' => $validated['display_name_kh'],
                    'restricted' => $validated['restricted'] ?? false,
                ]);

                if (isset($validated['permission_ids'])) {
                    $role->assignPermissions($validated['permission_ids']);
                }

                return $role;
            });

            $role->load('permissions');
            return ApiResponse::success($role, 'Role created successfully', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Failed to create role: ' . $e->getMessage());
            return ApiResponse::error('Failed to create role');
        }
    }

    /**
     * Update a role.
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:121|unique:roles,name,' . $role->id,
                'display_name' => 'sometimes|required|string|max:121',
                'display_name_kh' => 'sometimes|required|string|max:121',
                'restricted' => 'sometimes|boolean',
                'permission_ids' => 'sometimes|array',
                'permission_ids.*' => 'exists:permissions,id',
            ]);

            DB::transaction(function () use ($role, $validated) {
                $role->update(array_filter($validated, function ($key) {
                    return !in_array($key, ['permission_ids']);
                }, ARRAY_FILTER_USE_KEY));

                if (isset($validated['permission_ids'])) {
                    $role->assignPermissions($validated['permission_ids']);
                }
            });

            $role->load('permissions');
            return ApiResponse::success($role, 'Role updated successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Failed to update role: ' . $e->getMessage());
            return ApiResponse::error('Failed to update role');
        }
    }

    /**
     * Delete a role.
     */
    public function destroy(Role $role): JsonResponse
    {
        try {
            if ($role->restricted) {
                return ApiResponse::error('Cannot delete restricted role', 403);
            }

            if ($role->users()->exists()) {
                return ApiResponse::error('Cannot delete role that has users assigned', 422);
            }

            DB::transaction(function () use ($role) {
                $role->permissions()->detach();
                $role->delete();
            });

            return ApiResponse::success(null, 'Role deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete role: ' . $e->getMessage());
            return ApiResponse::error('Failed to delete role');
        }
    }

    /**
     * Get all permissions for a role.
     */
    public function permissions(Role $role): JsonResponse
    {
        try {
            $permissions = $role->permissions;
            return ApiResponse::success($permissions);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve role permissions: ' . $e->getMessage());
            return ApiResponse::error('Failed to retrieve role permissions');
        }
    }

    /**
     * Assign permissions to a role.
     */
    public function assignPermissions(Request $request, Role $role): JsonResponse
    {
        try {
            $validated = $request->validate([
                'permission_ids' => 'required|array',
                'permission_ids.*' => 'exists:permissions,id',
            ]);

            $role->assignPermissions($validated['permission_ids']);
            $role->load('permissions');

            return ApiResponse::success($role, 'Permissions assigned successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Failed to assign permissions: ' . $e->getMessage());
            return ApiResponse::error('Failed to assign permissions');
        }
    }

    /**
     * Remove permissions from a role.
     */
    public function removePermissions(Request $request, Role $role): JsonResponse
    {
        try {
            $validated = $request->validate([
                'permission_ids' => 'required|array',
                'permission_ids.*' => 'exists:permissions,id',
            ]);

            $role->removePermissions($validated['permission_ids']);
            $role->load('permissions');

            return ApiResponse::success($role, 'Permissions removed successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Failed to remove permissions: ' . $e->getMessage());
            return ApiResponse::error('Failed to remove permissions');
        }
    }
}
