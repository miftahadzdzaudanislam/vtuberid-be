<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Vtuber;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TagController extends Controller
{
    /**
     * Menampilkan daftar tag
     */
    public function daftarTag(Request $request)
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
     * Menampilkan data tag untuk admin
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tag $tag)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        //
    }
}
