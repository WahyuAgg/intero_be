<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\GoogleAuthController;

class AuthController extends Controller
{
    /**
     * Handle a login request and return a Sanctum API token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {

            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        // tentukan kapan access_token expired
        $token_expired = Carbon::now()->addHours(12);

        // Store expiration date
        $user->tokens->last()->update([
            'expires_at' => $token_expired, // Expire in 1 hour
        ]);

        // Refresh Google token
        $googleAuthController = new GoogleAuthController();
        $googleRefreshResponse = $googleAuthController->refreshToken($user->id);

        return response()->json([
            'access_token' => $token,
            'user' => $user,
            'token_type' => 'Bearer',
            'expires_at' => $token_expired,
            'googs_token' => $googleRefreshResponse->original,
        ]);
    }


    // Register User
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }


    // Logout
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
