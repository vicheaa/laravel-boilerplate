<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ApiResponse;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user and return a token.
     */
    public function signup(StoreUserRequest $request): JsonResponse
    {
        try {
            $user = DB::transaction(fn() => User::create($request->validated()));

            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ]);
        } catch (\Exception $e) {
            Log::error('Signup failed: ' . $e->getMessage()); // Log the actual error
            return ApiResponse::error(message: 'Registration failed. Please try again later.');
        }
    }

    /**
     * Create a new user (admin only).
     */
    public function createUser(StoreUserRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $user = DB::transaction(function () use ($validated) {
                $user = User::create([
                    'name'      => $validated['name'],
                    'email'     => $validated['email'],
                    'password'  => $validated['password'],
                ]);

                // Assign role if provided
                if (isset($validated['role_id'])) {
                    $role = Role::find($validated['role_id']);
                    if ($role) {
                        $user->assignRole($role);
                    }
                }

                return $user;
            });

            $user->load('role');
            return ApiResponse::success($user, 'User created successfully', 201);
        } catch (\Exception $e) {
            Log::error('User creation failed: ' . $e->getMessage());
            return ApiResponse::error('Failed to create user');
        }
    }

    /**
     * Authenticate a user and return a token.
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'     => 'required|email',
            'password'  => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Log the user out by deleting their current token.
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->tokens()->delete();
            return ApiResponse::success(message: 'Logged out successfully.');
        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage());
            return ApiResponse::error(message: 'Failed to log out. Please try again.');
        }
    }

    /**
     * Get the authenticated user's profile.
     */
    public function profile(Request $request): JsonResponse
    {
        // No try-catch needed here. The 'auth' middleware already ensures a user exists.
        // If something else fails, Laravel's global exception handler will catch it.
        return ApiResponse::success($request->user());
    }

    /**
     * Get a paginated list of all users.
     * @throws AuthorizationException
     */
    public function getAllUsers(Request $request): JsonResponse
    {
        try {
            $pageSize = $request->input('page_size', 15);
            $users = User::with('role')->paginate($pageSize);

            return ApiResponse::paginated($users);
        } catch (AuthorizationException $e) {
            // Be specific with authorization failures (403 Forbidden)
            return ApiResponse::error(message: 'You are not authorized to perform this action.', code: 403);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve users: ' . $e->getMessage());
            // Catch any other potential errors (e.g., database connection)
            return ApiResponse::error(message: 'Could not retrieve users.');
        }
    }

    /**
     * Update user role.
     */
    public function updateUserRole(Request $request, User $user): JsonResponse
    {
        try {
            $validated = $request->validate([
                'role_id' => 'required|exists:roles,id',
            ]);

            $user->assignRole(Role::find($validated['role_id']));
            $user->load('role');

            return ApiResponse::success($user, 'User role updated successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Failed to update user role: ' . $e->getMessage());
            return ApiResponse::error('Failed to update user role');
        }
    }

    /**
     * Remove user role.
     */
    public function removeUserRole(User $user): JsonResponse
    {
        try {
            $user->removeRole();
            $user->load('role');

            return ApiResponse::success($user, 'User role removed successfully');
        } catch (\Exception $e) {
            Log::error('Failed to remove user role: ' . $e->getMessage());
            return ApiResponse::error('Failed to remove user role');
        }
    }

    /**
     * Get user permissions.
     */
    public function getUserPermissions(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $permissions = $user->getPermissions();

            return ApiResponse::success($permissions);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve user permissions: ' . $e->getMessage());
            return ApiResponse::error('Failed to retrieve user permissions');
        }
    }
}
