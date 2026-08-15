<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationSocialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrganizationSocialAccountController extends Controller
{
    public function store(Request $request, string $org)
    {
        // Find Organization
        $org = Organization::find($org);
        if (!$org) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found',
            ], 404);
        }

        // Validator
        $validator = Validator::make($request->all(), [
            'platform_id' => 'required|exists:platforms,id',
            'username' => 'required|string|max:255',
            'url' => 'required|url|max:500',
        ]);

        // Check Validator errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // Check duplicate platform
        $duplicate = $org->orgSocialAccounts()->where('platform_id', $request->platform_id)->exists();
        if ($duplicate) {
            return response()->json([
                'success' => false,
                'message' => 'This Organization already has a social account for this platform.'
            ], 409);
        }

        // Create social account
        $socialAccount = $org->orgSocialAccounts()->create([
            'platform_id' => $request->platform_id,
            'username' => $request->username,
            'url' => $request->url,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Social account organization added successfully!',
            'data' => $socialAccount->load([
                'platform:id,name,slug',
            ]),
        ], 201);
    }

    public function update(Request $request, Organization $organization, string $socialAccountId)
    {
        // Check Social account
        $socialAccount = OrganizationSocialAccount::find($socialAccountId);
        if (!$socialAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Social account not found.',
            ], 404);
        }

        // Check relationship
        if ($socialAccount->organization_id !== $organization->id) {
            return response()->json([
                'success' => false,
                'message' => 'This social account is not associated with this organization.'
            ], 409);
        }

        // Validator
        $validator = Validator::make($request->all(), [
            'platform_id' => 'required|exists:platforms,id',
            'username' => 'required|string|max:255',
            'url' => 'required|url|max:500',
        ]);

        // Check Validator errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // Check duplicate platform
        $duplicate = $organization->orgSocialAccounts()
            ->where('platform_id', $request->platform_id)
            ->where('id', '!=', $socialAccount->id)
            ->exists();
        if ($duplicate) {
            return response()->json([
                'success' => false,
                'message' => 'This Organization already has a social account for this platform.'
            ], 409);
        }

        // Update
        $socialAccount->update([
            'platform_id' => $request->platform_id,
            'username' => $request->username,
            'url' => $request->username,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Social account organization updated successfully!',
            'data' => $socialAccount->load([
                'platform:id,name,slug',
            ]),
        ], 200);
    }

    public function destroy(Organization $organization, string $socialAccountId)
    {
        // Check Social account
        $socialAccount = OrganizationSocialAccount::find($socialAccountId);
        if (!$socialAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Social account not found.',
            ], 404);
        }

        // Check relationship
        if ($socialAccount->organization_id !== $organization->id) {
            return response()->json([
                'success' => false,
                'message' => 'This social account is not associated with this organization.',
            ], 404);
        }

        // Remove relationship
        $socialAccount->delete();

        return response()->json([
            'success' => true,
            'message' => 'Social account organization removed successfully!',
        ], 200);
    }
}
