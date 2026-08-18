<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
     * Create a new platform
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Generate slug jika user tidak mengisi slug
        $slug = $request->filled('slug') ? $request->slug : Str::slug($request->name);

        // Validator
        $validator = Validator::make(array_merge($request->all(), [
            'slug' => $slug
        ]), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:platforms,slug',
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

        // handle image icon
        $iconPath = null;
        if ($request->hasFile('icon')) {
            $icon = $request->file('icon');
            $ext = $icon->getClientOriginalExtension();
            $filename = Str::slug($request->name) . '_' . time() . '.' . $ext;
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
     * Detail Platform berdasarkan ID
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
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
     * Update platform
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        // Find platform
        $platform = Platform::find($id);
        if (!$platform) {
            return response()->json([
                'success' => false,
                'message' => 'Platform not found',
            ], 404);
        }

        // Generate slug jika user tidak mengisi slug
        $slug = $request->filled('slug') ? $request->slug : Str::slug($request->name);

        // Validator
        $validator = Validator::make(array_merge($request->all(), [
            'slug' => $slug
        ]), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|' . Rule::unique('platforms', 'slug')->ignore($platform->id),
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

        // siapkan data yang ingin di update
        $data = [
            'name' => $request->name,
            'slug' => $slug,
            'base_url' => $request->base_url
        ];

        // handle image icon
        if ($request->hasFile('icon')) {
            $icon = $request->file('icon');
            $ext = $icon->getClientOriginalExtension();
            $filename = Str::slug($request->name) . '_' . time() . '.' . $ext;
            $iconPath = $icon->storeAs('platforms/icon', $filename, 'public');

            // Hanya hapus icon hasil upload custom
            if (
                $platform->icon &&
                !str_starts_with($platform->icon, 'platforms/default/')
            ) {
                Storage::disk('public')->delete($platform->icon);
            }
            $data['icon'] = $iconPath;
        }

        $platform->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Platform updated successfully!',
            'data' => $platform->fresh()
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find platform
        $platform = Platform::find($id);
        if (!$platform) {
            return response()->json([
                'success' => false,
                'message' => 'Platform not found',
            ], 404);
        }

        // Hanya hapus icon hasil upload custom
        if (
            $platform->icon &&
            !str_starts_with($platform->icon, 'platforms/default/')
        ) {
            Storage::disk('public')->delete($platform->icon);
        }

        $platform->delete();

        return response()->json([
            'success' => true,
            'message' => 'Platform deleted successfully!',
        ], 200);
    }
}
