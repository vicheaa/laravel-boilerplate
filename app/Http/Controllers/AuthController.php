<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ApiResponse;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function createUser(StoreUserRequest $request)
    {
        try {
            $user = User::create($request->validated());

            return ApiResponse::success($user, 'User created successfully', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to create user', 500, $e->getMessage());
        }
    }

    public function signup(StoreUserRequest $request)
    {
        try {
            $fields = $request->validated();
            $user = User::create([
                'name' => $fields['name'],
                'email' => $fields['email'],
                'password' => bcrypt($fields['password']),
            ]);
            $token      = $user->createToken('myapptoken')->plainTextToken;
            $response   = [
                'user'  => $user,
                'token' => $token
            ];

            return response($response, 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to create user', 500, $e->getMessage());
        }
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'Bad Credentials',
            ], 401);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();
        return response()->json(['success' => true]);
    }

    public function profile()
    {
        try {
            if (Auth::check()) {
                $profile = Auth::user();
                return ApiResponse::success($profile);
            } else {
                return ApiResponse::error('Unauthorized', 401);
            }
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve profile', 500);
        }
    }

    public function getAllUsers(Request $request)
    {
        try {
            $pageSize = $request->input('page_size', 10);
            $users = User::paginate($pageSize);
            return ApiResponse::paginated($users);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve users', 500, $e->getMessage());
        }
    }
}
