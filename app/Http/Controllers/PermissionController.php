<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ApiResponse;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PermissionController extends Controller
{
    /**
     * Get all permissions with their relationships.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $permissions = Permission::with(['parent', 'children', 'roles'])->get();
            return ApiResponse::success($permissions);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve permissions: ' . $e->getMessage());
            return ApiResponse::error('Failed to retrieve permissions');
        }
    }

    /**
     * Get a specific permission with its relationships.
     */
    public function show(Permission $permission): JsonResponse
    {
        try {
            $permission->load(['parent', 'children', 'roles']);
            return ApiResponse::success($permission);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve permission: ' . $e->getMessage());
            return ApiResponse::error('Failed to retrieve permission');
        }
    }

    /**
     * Create a new permission.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:121|unique:permissions,name',
                'display_name' => 'required|string|max:121',
                'display_name_kh' => 'required|string|max:121',
                'action' => 'required|string|max:121',
                'subject' => 'required|string|max:121',
                'description' => 'nullable|string',
                'description_kh' => 'nullable|string',
                'parent_id' => 'nullable|exists:permissions,id',
            ]);

            $permission = Permission::create($validated);
            $permission->load(['parent', 'children']);

            return ApiResponse::success($permission, 'Permission created successfully', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Failed to create permission: ' . $e->getMessage());
            return ApiResponse::error('Failed to create permission');
        }
    }

    /**
     * Update a permission.
     */
    public function update(Request $request, Permission $permission): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:121|unique:permissions,name,' . $permission->id,
                'display_name' => 'sometimes|required|string|max:121',
                'display_name_kh' => 'sometimes|required|string|max:121',
                'action' => 'sometimes|required|string|max:121',
                'subject' => 'sometimes|required|string|max:121',
                'description' => 'nullable|string',
                'description_kh' => 'nullable|string',
                'parent_id' => 'nullable|exists:permissions,id',
            ]);

            $permission->update($validated);
            $permission->load(['parent', 'children']);

            return ApiResponse::success($permission, 'Permission updated successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Failed to update permission: ' . $e->getMessage());
            return ApiResponse::error('Failed to update permission');
        }
    }

    /**
     * Delete a permission.
     */
    public function destroy(Permission $permission): JsonResponse
    {
        try {
            if ($permission->children()->exists()) {
                return ApiResponse::error('Cannot delete permission that has child permissions', 422);
            }

            if ($permission->roles()->exists()) {
                return ApiResponse::error('Cannot delete permission that is assigned to roles', 422);
            }

            DB::transaction(function () use ($permission) {
                $permission->roles()->detach();
                $permission->delete();
            });

            return ApiResponse::success(null, 'Permission deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete permission: ' . $e->getMessage());
            return ApiResponse::error('Failed to delete permission');
        }
    }

    /**
     * Get permissions in a hierarchical structure.
     */
    public function hierarchy(): JsonResponse
    {
        try {
            $permissions = Permission::with(['children', 'roles'])
                ->whereNull('parent_id')
                ->get();

            return ApiResponse::success($permissions);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve permission hierarchy: ' . $e->getMessage());
            return ApiResponse::error('Failed to retrieve permission hierarchy');
        }
    }

    /**
     * Get permissions by action.
     */
    public function byAction(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'action' => 'required|string',
            ]);

            $permissions = Permission::where('action', $validated['action'])
                ->with(['parent', 'children', 'roles'])
                ->get();

            return ApiResponse::success($permissions);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Failed to retrieve permissions by action: ' . $e->getMessage());
            return ApiResponse::error('Failed to retrieve permissions by action');
        }
    }

    /**
     * Get permissions by subject.
     */
    public function bySubject(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'subject' => 'required|string',
            ]);

            $permissions = Permission::where('subject', $validated['subject'])
                ->with(['parent', 'children', 'roles'])
                ->get();

            return ApiResponse::success($permissions);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Failed to retrieve permissions by subject: ' . $e->getMessage());
            return ApiResponse::error('Failed to retrieve permissions by subject');
        }
    }
}
