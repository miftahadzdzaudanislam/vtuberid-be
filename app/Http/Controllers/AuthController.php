<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;

class AuthController extends Controller
{
    /**
     * Login
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request) {
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

        return $this->respondWithToken($token, 'Login successfully!');
    }

    /**
     * Ambil data user login
     * @return \Illuminate\Http\JsonResponse
     */
    public function me() {
        return response()->json([
            'success' => true,
            'user' => auth('api')->user()
        ], 200);
    }

    /**
     * Logout
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
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
    public function refresh() {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());

            return $this->respondWithToken($token, 'Token refreshed successfully!');
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
    protected function respondWithToken($token, $message = '') {
        return response()->json([
            'success' => true,
            'message' => $message,
            'user' => auth('api')->user(),
            'access_token' => $token,
            'token_type' => 'bearer',
            'expired_in' => JWTAuth::factory()->getTTL() * 60,
        ]);
    }
}
