<?php

namespace App\Http\Controllers\Vtuber;

use App\Http\Controllers\Controller;
use App\Models\Vtuber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VtuberOrganizationController extends Controller
{
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

    public function update(Request $request, Vtuber $vtuber, string $id) {}

    public function delete(Vtuber $vtuber, string $id) {}
}
