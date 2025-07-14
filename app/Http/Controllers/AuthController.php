<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('api-token')->plainTextToken;
            return response()->json(['token' => $token]);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
    public function logout()
    {
        Auth::logout();
        return response()->json(['success' => true]);
    }

    public function profile()
    {
        // return ApiResponse::success(Auth::user());
        return response()->json(Auth::user());
    }

    public function getAllUsers(Request $request)
    {
        $pageSize = $request->input('page_size', 10);
        $users = User::paginate($pageSize);
        return ApiResponse::paginated($users);
    }
}
