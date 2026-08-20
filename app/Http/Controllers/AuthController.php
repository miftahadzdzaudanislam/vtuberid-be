<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Login
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $token = auth()->guard('api')->attempt(
            $request->only('email', 'password')
        );

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Login Failed: Invalid email or password'
            ], 401);
        }

        $user = User::findOrFail(auth('api')->id());

        // Cek status akun
        if ($user->status !== 'active') {
            try {
                JWTAuth::invalidate($token);
            } catch (JWTException $e) {
                // Ignore invalidation errors during inactive-account rejection.
            }

            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive.',
            ], 403);
        }

        // Update hanya ketika LOGIN berhasil
        $user->update([
            'last_login_at' => now(),
        ]);

        $user->refresh();

        return $this->respondWithToken($token, 'Login successfully!', $user);
    }

    /**
     * Ambil data user login
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = auth('api')->user();

        return response()->json([
            'success' => true,
            'user' => $user
        ], 200);
    }

    /**
     * Logout
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Logout successfully!'
            ], 200);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout Failed'
            ], 500);
        }
    }

    /**
     * Refresh token
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        try {
            // Ambil user dari token lama terlebih dahulu
            $user = auth('api')->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token.',
                ], 401);
            }

            // Cek status akun sebelum refresh
            if ($user->status !== 'active') {
                JWTAuth::invalidate(JWTAuth::getToken());

                return response()->json([
                    'success' => false,
                    'message' => 'Your account is inactive.',
                ], 403);
            }

            $token = JWTAuth::refresh(JWTAuth::getToken());

            return $this->respondWithToken($token, 'Token refreshed successfully!', $user);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired, please login again.',
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token.',
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not refresh token.',
            ], 500);
        }
    }

    /**
     * Format response token
     * @param mixed $token
     * @param mixed $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $message = '', $user = null)
    {
        if (!$user) {
            $user = auth('api')->user();
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expired_in' => JWTAuth::factory()->getTTL() * 60,
        ]);
    }
}
