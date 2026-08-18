<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TagController extends Controller
{
    /**
     * Menampilkan seluruh data tag
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 50);

        $tags = QueryBuilder::for(Tag::class)
            ->allowedFilters(
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('name', 'like', "%{$value}%");
                    $query->where('slug', 'like', "%{$value}%");
                }),
            )->withCount('vtubers')
            ->orderBy('updated_at', 'desc')
            ->paginate($limit)
            ->appends($request->query());

        if ($tags->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'No Tag data found!'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get all tags',
            'data' => $tags->items(),
            'paggination' => [
                'total' => $tags->total(),
                'per_page' => $tags->perPage(),
                'current_page' => $tags->currentPage(),
                'last_page' => $tags->lastPage(),
                'from' => $tags->firstItem(),
                'to' => $tags->lastItem()
            ]
        ], 200);
    }

    /**
     * Create a new tag
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
            'slug' => 'required|string|max:255|unique:tags,slug'
        ]);

        // Check Validator errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // Insert data
        $tag = Tag::create([
            'name' => $request->name,
            'slug' => $slug,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tag created successfully!',
            'data' => $tag
        ], 201);
    }

    /**
     * Menampilkan detail tag berdasarkan ID
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        // Search Tag
        $tag = Tag::withCount('vtubers')->find($id);

        if (!$tag) {
            return response()->json([
                'success' => false,
                'message' => 'Tag not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get tag details',
            'data' => $tag,
        ], 200);
    }

    /**
     * Update tag
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        // Find Tag
        $tag = Tag::find($id);
        if (!$tag) {
            return response()->json([
                'success' => false,
                'message' => 'Tag not found',
            ], 404);
        }

        // Generate slug jika user tidak mengisi slug
        $slug = $request->filled('slug') ? $request->slug : Str::slug($request->name);

        // Validator
        $validator = Validator::make(array_merge($request->all(), [
            'slug' => $slug
        ]), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|' . Rule::unique('tags', 'slug')->ignore($tag->id)
        ]);

        // Check Validator errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // update tag
        $tag->update([
            'name' => $request->name,
            'slug' => $slug,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tag updated successfully!',
            'data' => $tag
        ], 200);
    }

    /**
     * Delete tag
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        // Find Tag
        $tag = Tag::find($id);
        if (!$tag) {
            return response()->json([
                'success' => false,
                'message' => 'Tag not found',
            ], 404);
        }

        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag deleted successfully!',
        ], 200);
    }
}
