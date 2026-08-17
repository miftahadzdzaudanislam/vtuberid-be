<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PlatformController extends Controller
{
    /**
     * Menampilkan seluruh data platform
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 50);

        $platforms = QueryBuilder::for(Platform::class)
            ->allowedFilters(
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('name', 'like', "%{$value}%");
                    $query->where('slug', 'like', "%{$value}%");
                }),
            )->orderBy('updated_at', 'desc')
            ->paginate($limit)
            ->appends($request->query());

        if ($platforms->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'No Platform data found!'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get all platforms',
            'data' => $platforms->items(),
            'paggination' => [
                'total' => $platforms->total(),
                'per_page' => $platforms->perPage(),
                'current_page' => $platforms->currentPage(),
                'last_page' => $platforms->lastPage(),
                'from' => $platforms->firstItem(),
                'to' => $platforms->lastItem()
            ]
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validator
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tags,slug',
            'icon' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'base_url' => 'required|url|max:500'
        ]);

        // Check Validator errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // Generate slug jika tidak dikirim
        $slug = $request->slug ?? Str::slug($request->name);
        if (Platform::where('slug', $slug)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This platform already exists.',
            ], 409);
        }

        $iconPath = null;
        if ($request->hasFile('icon')) {
            $icon = $request->file('icon');
            $ext = $icon->getClientOriginalExtension();
            $filename = Str::slug($request->icon) . '_' . time() . '.' . $ext;
            $iconPath = $icon->storeAs('platforms/icon', $filename, 'public');
        }

        // Insert data
        $platform = Platform::create([
            'name' => $request->name,
            'slug' => $slug,
            'icon' => $iconPath,
            'base_url' => $request->base_url
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Platform created successfully!',
            'data' => $platform
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Search Platform
        $platform = Platform::find($id);

        if (!$platform) {
            return response()->json([
                'success' => false,
                'message' => 'Platform not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get platform details',
            'data' => $platform,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Platform $platform)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Platform $platform)
    {
        //
    }
}
