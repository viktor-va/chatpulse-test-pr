<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\User;

class AuthController
{
    public function issueToken(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('personal-access')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    public function revokeCurrentToken(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->currentAccessToken()) {
            return response()->json(['message' => 'No active token'], 400);
        }

        $user->currentAccessToken()->delete();

        return response()->noContent(); // 204
    }
}
