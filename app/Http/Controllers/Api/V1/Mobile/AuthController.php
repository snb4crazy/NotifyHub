<?php

namespace App\Http\Controllers\Api\V1\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\MobileLoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * Issue a Sanctum token for the mobile client.
     */
    public function store(MobileLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken($credentials['device_name'] ?? config('notifyhub.mobile.token_name', 'mobile'));

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Revoke the current mobile token.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'status' => 'logged_out',
        ]);
    }
}
