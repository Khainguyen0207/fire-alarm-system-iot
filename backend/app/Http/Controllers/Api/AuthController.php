<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->validated('email'))->first();

        if ($user === null || ! Hash::check($request->validated('password'), $user->password)) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'The provided credentials are incorrect.',
                'errors' => null,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $user->createToken('admin-api')->plainTextToken,
                'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
            ],
            'message' => 'Logged in successfully.',
            'errors' => null,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Logged out successfully.',
            'errors' => null,
        ]);
    }
}
