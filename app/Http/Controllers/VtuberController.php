<?php

namespace App\Http\Controllers;

use App\Models\Vtuber;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class VtuberController extends Controller
{
    /**
     * Menampilkan daftar vtuber
     */
    public function daftarVtuber(Request $request)
    {
        $limit = $request->input('limit', 50);

        $vtubers = QueryBuilder::for(Vtuber::class)
            ->allowedFilters(
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('name', 'like', "%{$value}%");
                    $query->orWhere('slug', 'like', "%{$value}%");
                }),
                AllowedFilter::exact('status'),
            )->with([
                'tags:id,name,slug',
                'organizations' => function ($query) {
                    $query->select(
                        'organizations.id',
                        'organizations.name',
                        'organizations.slug',
                        'organizations.logo'
                    )->wherePivot('status', 'active');
                },
            ])->orderBy('name', 'asc')
            ->paginate($limit)
            ->appends($request->query());

        if ($vtubers->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'No Vtubers data found!'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get all vtubers',
            'data' => $vtubers->items(),
            'paggination' => [
                'total' => $vtubers->total(),
                'per_page' => $vtubers->perPage(),
                'current_page' => $vtubers->currentPage(),
                'last_page' => $vtubers->lastPage(),
                'from' => $vtubers->firstItem(),
                'to' => $vtubers->lastItem()
            ]
        ], 200);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function show(Vtuber $vtuber)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vtuber $vtuber)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vtuber $vtuber)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vtuber $vtuber)
    {
        //
    }
}
