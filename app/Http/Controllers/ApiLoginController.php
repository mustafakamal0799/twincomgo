<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ApiLoginController extends Controller
{
    /**
     * Handle user login and return API token for frontend use.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Email atau password salah!'
            ], 401);
        }

        $user = Auth::user();

        // Optional: delete previous tokens to allow single session
        $user->tokens()->delete();

        $token = $user->createToken('frontend-api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil!',
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * Handle user logout by deleting current access token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }
}
