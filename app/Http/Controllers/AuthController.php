<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => 'Email not registered.'], 404);
        }
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Incorrect password.'], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'role' => $user->role,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'role' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8'
        ], [
            'email.unique' => 'This email address is already registered.',
        ]);
        $user = User::create([
            'role'=>$request->role,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);

    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user || !$user->currentAccessToken()) {
                return response()->json([
                    'message' => 'Unauthenticated — no valid token found.'
                ], 401);
            }

            $user->currentAccessToken()->delete();

            return response()->json(['message' => 'Logged out successfully']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout failed: ' . $e->getMessage()
            ], 500);
        }
    }

}
