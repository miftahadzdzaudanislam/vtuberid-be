<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OrganizationController extends Controller
{
    /**
     * Menampilkan daftar organization
     */
    public function daftarOrganization(Request $request)
    {
        $limit = $request->input('limit', 50);

        $organizations = QueryBuilder::for(Organization::class)
            ->select([
                'id',
                'name',
                'slug',
                'logo',
            ])
            ->allowedFilters(
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('name', 'like', "%$value%");
                    $query->where('slug', 'like', "%$value%");
                }),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('type'),
            )->with([
                'vtubers:id,avatar'
            ])->withCount([
                'vtubers as talent_count' => function ($query) {
                    $query->where('organization_members.status', 'active');
                }
            ])->orderBy('name', 'asc')
            ->paginate($limit)
            ->appends($request->query());

        if ($organizations->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Tidak ada data Organisasi ditemukan!'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get all organizations',
            'data' => $organizations->items(),
            'paggination' => [
                'total' => $organizations->total(),
                'per_page' => $organizations->perPage(),
                'current_page' => $organizations->currentPage(),
                'last_page' => $organizations->lastPage(),
                'from' => $organizations->firstItem(),
                'to' => $organizations->lastItem()
            ]
        ], 200);
    }

    /**
     * Menampilkan data organisasi untuk admin
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 50);

        $organizations = QueryBuilder::for(Organization::class)
            ->allowedFilters(
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('name', 'like', "%$value%");
                    $query->where('slug', 'like', "%$value%");
                }),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('type'),
            )->with([
                'vtubers:id,avatar',
            ])->withCount([
                'vtubers as talent_count' => function ($query) {
                    $query->where('organization_members.status', 'active');
                }
            ])->orderBy('name', 'asc')
            ->paginate($limit)
            ->appends($request->query());

        if ($organizations->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'No Organization data found!'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get all organizations',
            'data' => $organizations->items(),
            'paggination' => [
                'total' => $organizations->total(),
                'per_page' => $organizations->perPage(),
                'current_page' => $organizations->currentPage(),
                'last_page' => $organizations->lastPage(),
                'from' => $organizations->firstItem(),
                'to' => $organizations->lastItem()
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
    public function show(Organization $organization)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Organization $organization)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organization $organization)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organization $organization)
    {
        //
    }
}
