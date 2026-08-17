<?php

namespace App\Http\Controllers\Vtuber;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Vtuber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VtuberOrganizationController extends Controller
{
    /**
     * Create a new organization member
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

        // Find Organization
        $organization = Organization::find($request->organization_id);
        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found',
            ], 404);
        }

        // Validator
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'generation' => 'nullable|string|max:100',
            'joined_at' => 'nullable|date',
            'left_at' => 'nullable|date',
            'status' => 'required|in:active,graduated,left',
        ]);

        // Check Validator errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // Check duplicate relationship
        $duplicate = $vtuber->organizations()->where('organizations.id', $request->organization_id)->exists();
        if ($duplicate) {
            return response()->json([
                'success' => false,
                'message' => 'This organization is already associated with this vtuber.'
            ], 409);
        }

        // Add organization
        $vtuber->organizations()->attach(
            $request->organization_id,
            [
                'generation' => $request->generation,
                'joined_at' => $request->joined_at,
                'left_at' => $request->left_at,
                'status' => $request->status,
            ]
        );

        // Update Affiliate
        $vtuber->updateCurrentAffiliation();
        return response()->json([
            'success' => true,
            'message' => 'Organization added successfully!',
            'data' => $vtuber->load([
                'organizations:id,name,slug,type,logo',
            ]),
        ], 201);
    }

    /**
     * Update organization member
     * @param Request $request
     * @param string $vtuberId
     * @param string $organizationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $vtuberId, string $organizationId)
    {
        // Find Vtuber
        $vtuber = Vtuber::find($vtuberId);
        if (!$vtuber) {
            return response()->json([
                'success' => false,
                'message' => 'Vtuber not found',
            ], 404);
        }

        // Check organization
        $organization = Organization::find($organizationId);
        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found or not associated with this vtuber.',
            ], 404);
        }

        // Validator
        $validator = Validator::make($request->all(), [
            'generation' => 'nullable|string|max:100',
            'joined_at' => 'nullable|date',
            'left_at' => 'nullable|date',
            'status' => 'required|in:active,graduated,left',
        ]);

        // Check Validator errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // Check relationship
        $relationship = $vtuber->organizations()->where('organizations.id', $organization->id)->exists();
        if (!$relationship) {
            return response()->json([
                'success' => false,
                'message' => 'This organization is not associated with this vtuber.',
            ], 404);
        }

        // Update pivot
        $vtuber->organizations()->updateExistingPivot(
            $organization->id,
            [
                'generation' => $request->generation,
                'joined_at' => $request->joined_at,
                'left_at' => $request->left_at,
                'status' => $request->status,
            ]
        );

        // Update Affiliate
        $vtuber->updateCurrentAffiliation();
        return response()->json([
            'success' => true,
            'message' => 'Organization relationship updated successfully!',
            'data' => $vtuber->load([
                'organizations:id,name,slug,type,logo',
            ]),
        ], 200);
    }

    /**
     * Delete organization member
     * @param string $vtuberId
     * @param string $organizationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $vtuberId, string $organizationId)
    {
        // Find Vtuber
        $vtuber = Vtuber::find($vtuberId);
        if (!$vtuber) {
            return response()->json([
                'success' => false,
                'message' => 'Vtuber not found',
            ], 404);
        }

        // Check organization
        $organization = Organization::find($organizationId);
        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found or This organization is not associated with this vtuber.',
            ], 404);
        }

        // Check relationship
        $relationship = $vtuber->organizations()->where('organizations.id', $organization->id)->exists();
        if (!$relationship) {
            return response()->json([
                'success' => false,
                'message' => 'This organization is not associated with this vtuber.',
            ], 404);
        }

        // Remove relationship
        $vtuber->organizations()->detach($organization->id);

        // Update affiliation
        $vtuber->updateCurrentAffiliation();
        return response()->json([
            'success' => true,
            'message' => 'Organization removed successfully!',
            'data' => [
                'current_affiliation' => $vtuber->current_affiliation,
            ],
        ], 200);
    }
}
