<?php

namespace App\Http\Controllers\Vtuber;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use App\Models\SocialAccount;
use App\Models\Vtuber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VtuberSocialAccountController extends Controller
{
    /**
     * Create a new social account vtuber
     * @param Request $request
     * @param string $vtuber
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, string $vtuber)
    {
        // Find Vtuber
        $vtuber = Vtuber::find($vtuber);
        if (!$vtuber) {
            return response()->json([
                'success' => false,
                'message' => 'Vtuber not found',
            ], 404);
        }

        // Find Platform
        $platform = Platform::find($request->platform_id);
        if (!$platform) {
            return response()->json([
                'success' => false,
                'message' => 'Plaftorm not found',
            ], 404);
        }

        // Validator
        $validator = Validator::make($request->all(), [
            'platform_id' => 'required|exists:platforms,id',
            'username' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'followers' => 'nullable|numeric|min:0',
        ]);

        // Check Validator errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // Check duplicate platform
        $duplicate = $vtuber->socialAccounts()->where('platform_id', $request->platform_id)->exists();
        if ($duplicate) {
            return response()->json([
                'success' => false,
                'message' => 'This VTuber already has a social account for this platform.'
            ], 409);
        }

        // Create social account
        $socialAccount = $vtuber->socialAccounts()->create([
            'platform_id' => $request->platform_id,
            'username' => $request->username,
            'url' => $request->url,
            'followers' => $request->followers,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Social account added successfully!',
            'data' => $socialAccount->load([
                'platform:id,name,slug',
            ]),
        ], 201);
    }

    /**
     * Update social account vtuber
     * @param Request $request
     * @param string $vtuberId
     * @param string $socialAccountId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $vtuberId, string $socialAccountId)
    {
        // Check Vtuber
        $vtuber = Vtuber::find($vtuberId);
        if (!$vtuber) {
            return response()->json([
                'success' => false,
                'message' => 'Vtuber not found.',
            ], 404);
        }

        // Check Social account
        $socialAccount = SocialAccount::find($socialAccountId);
        if (!$socialAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Social account not found.',
            ], 404);
        }

        // Check relationship
        if ($socialAccount->vtuber_id !== $vtuber->id) {
            return response()->json([
                'success' => false,
                'message' => 'This social account is not associated with this vtuber.'
            ], 409);
        }

        // Validator
        $validator = Validator::make($request->all(), [
            'platform_id' => 'required|exists:platforms,id',
            'username' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'followers' => 'nullable|numeric|min:0',
        ]);

        // Check Validator errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // Check duplicate platform
        $duplicate = $vtuber->socialAccounts()
            ->where('platform_id', $request->platform_id)
            ->where('id', '!=', $socialAccount->id)
            ->exists();
        if ($duplicate) {
            return response()->json([
                'success' => false,
                'message' => 'This VTuber already has a social account for this platform.'
            ], 409);
        }

        // Update
        $socialAccount->update([
            'platform_id' => $request->platform_id,
            'username' => $request->username,
            'url' => $request->username,
            'followers' => $request->followers,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Social account updated successfully!',
            'data' => $socialAccount->load([
                'platform:id,name,slug',
            ]),
        ], 200);
    }

    /**
     * Delete social account vtuber
     * @param string $vtuberId
     * @param string $socialAccountId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $vtuberId, string $socialAccountId)
    {
        // Find Vtuber
        $vtuber = Vtuber::find($vtuberId);
        if (!$vtuber) {
            return response()->json([
                'success' => false,
                'message' => 'Vtuber not found',
            ], 404);
        }

        // Check Social account
        $socialAccount = SocialAccount::find($socialAccountId);
        if (!$socialAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Social account not found.',
            ], 404);
        }

        // Check relationship
        if ($socialAccount->vtuber_id !== $vtuber->id) {
            return response()->json([
                'success' => false,
                'message' => 'This social account is not associated with this vtuber.',
            ], 404);
        }

        // Remove relationship
        $socialAccount->delete();

        return response()->json([
            'success' => true,
            'message' => 'Social account removed successfully!',
        ], 200);
    }
}
