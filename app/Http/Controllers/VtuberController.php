<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Vtuber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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
            ->select([
                'id',
                'name',
                'slug',
                'avatar',
                'status',
            ])
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
                    )->wherePivot('status', 'active');
                },
            ])->orderBy('name', 'asc')
            ->paginate($limit)
            ->appends($request->query());

        if ($vtubers->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Tidak ada data Vtuber ditemukan!'
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
     * Menampilkan data vtuber untuk admin
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 50);

        $vtubers = QueryBuilder::for(Vtuber::class)
            ->select([
                'id',
                'name',
                'slug',
                'description',
                'avatar',
                'gender',
                'debut_date',
                'status',
                'current_affiliation',
            ])
            ->allowedFilters(
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('name', 'like', "%{$value}%");
                    $query->orWhere('slug', 'like', "%{$value}%");
                }),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('gender'),

                AllowedFilter::callback('organization_id', function ($query, $value) {
                    $query->whereHas('organizations', function ($query) use ($value) {
                        $query->where('organizations.id', $value)
                            ->where('organization_members.status', 'active');
                    });
                }),
            )->with([
                'tags:id,name,slug',
                'organizations' => function ($query) {
                    $query->select(
                        'organizations.id',
                        'organizations.name',
                        'organizations.slug',
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
     * Create a new vtuber
     */
    public function store(Request $request)
    {
        // Validator
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:vtubers,slug',
            'description' => 'nullable|string',
            'gender' => 'required|in:male,female',
            'debut_date' => 'nullable|date',
            'birthday' => 'nullable|date_format:m-d',
            'status' => 'required|in:active,inactive,hiatus,graduated,retired,unknown',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            'organizations' => 'nullable|array',
            'organizations.*.organization_id' => 'required|exists:organizations,id',
            'organizations.*.generation' => 'nullable|string|max:100',
            'organizations.*.joined_at' => 'nullable|date',
            'organizations.*.left_at' => 'nullable|date',
            'organizations.*.status' => 'required|in:active,graduated,left',

            'tag_ids' => 'nullable|string',

            'platforms' => 'nullable|array',
            'platforms.*.platform_id' => 'required|integer|exists:platforms,id',
            'platforms.*.username' => 'required|string|max:255',
            'platforms.*.url' => 'required|url|max:500',
        ]);

        // Check Validator errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // Get data
        $organizations = $request->input('organizations', []);
        $tagIds = $request->input('tag_ids', []);
        $platforms = $request->input('platforms', []);

        // Convert string to array
        if (is_string($organizations)) {
            $organizations = json_decode($organizations, true) ?? [];
        }
        if (is_string($tagIds)) {
            $tagIds = array_filter(
                array_map('intval', explode(',', $tagIds))
            );
        }
        if (is_string($platforms)) {
            $platforms = json_decode($platforms, true) ?? [];
        }

        // Set Current affiliation
        $currentAffiliation = collect($organizations)->contains(
            fn($organization) => isset($organization['status'])
                && $organization['status'] === 'active'
        ) ? 'organization' : 'independent';

        $avatarPath = null;
        $bannerPath = null;

        // Upload avatar & banner
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $ext = $avatar->getClientOriginalExtension();
            $filename = Str::slug($request->name) . '_' . time() . '.' . $ext;
            $avatarPath = $avatar->storeAs('vtubers/avatar', $filename, 'public');
        }

        if ($request->hasFile('banner')) {
            $banner = $request->file('banner');
            $ext = $banner->getClientOriginalExtension();
            $filename = Str::slug($request->name) . '_' . time() . '.' . $ext;
            $bannerPath = $banner->storeAs('vtubers/banner', $filename, 'public');
        }

        // Insert Vtuber
        $vtuber = Vtuber::create([
            'name' => $request->name,
            'slug' => $request->slug ?? Str::slug($request->name),
            'description' => $request->description,
            'gender' => $request->gender,
            'debut_date' => $request->debut_date,
            'birthday' => $request->birthday,
            'status' => $request->status,
            'current_affiliation' => $currentAffiliation,
            'avatar' => $avatarPath,
            'banner' => $bannerPath,
        ]);

        // Insert Organizations
        foreach ($organizations as $organization) {
            $vtuber->organizations()->attach(
                $organization['organization_id'],
                [
                    'generation' => $organization['generation'] ?? null,
                    'joined_at' => $organization['joined_at'] ?? null,
                    'left_at' => $organization['left_at'] ?? null,
                    'status' => $organization['status'],
                ]
            );
        }

        // Insert Tags
        if (!empty($tagIds)) {
            $vtuber->tags()->sync($tagIds);
        }

        // Insert Social Accounts
        foreach ($platforms as $account) {
            $vtuber->socialAccounts()->create([
                'platform_id' => $account['platform_id'],
                'username' => $account['username'],
                'url' => $account['url'],
            ]);
        }

        // dd($request->all());

        // Return respons json
        return response()->json([
            'success' => true,
            'message' => 'Vtuber created successfully!',
            'data' => $vtuber->load([
                'organizations:id,name,slug,logo',
                'tags:id,name,slug',
                'socialAccounts:id,vtuber_id,platform_id,username,url'
            ])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vtuber $vtuber)
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
