<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OrganizationController extends Controller
{
    /**
     * Menampilkan daftar organization
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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
                    $query->where('name', 'like', "%{$value}%");
                    $query->orWhere('slug', 'like', "%{$value}%");
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

        // Hilangkan pivot dari data vtuber
        $organizations->getCollection()->each(function ($org) {
            $org->vtubers->each->makeHidden('pivot');
        });

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
     * Menampilkan detail vtuber berdasarkan slug
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function detailOrganization(string $slug)
    {
        $organization = Organization::with([
            'vtubers:id,name,slug,status',
            'orgSocialAccounts:id,organization_id,platform_id,username,url',
            'orgSocialAccounts.platform:id,name,icon',
        ])->withCount('vtubers as talent_count')->where('slug', $slug)->first();

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get Organization details',
            'data' => $organization,
        ], 200);
    }

    /**
     *  Menampilkan data organisasi untuk admin
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 50);

        $organizations = QueryBuilder::for(Organization::class)
            ->allowedFilters(
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('name', 'like', "%{$value}%");
                    $query->orWhere('slug', 'like', "%{$value}%");
                }),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('type'),
            )->with([
                'vtubers' => function ($query) {
                    $query->select(
                        'vtubers.id',
                        'vtubers.avatar'
                    )
                        ->where('organization_members.status', 'active');
                },
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

        // Hilangkan pivot dari data vtubers
        $organizations->getCollection()->each(function ($organization) {
            $organization->vtubers->each->makeHidden('pivot');
        });

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
     * Create a new organization
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
            'slug' => 'required|string|max:255|unique:organizations,slug',
            'type' => 'required|in:agency,group',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'website' => 'nullable|url|max:500',
            'status' => 'required|in:active,inactive,liquidated',
        ]);

        // Check Validator errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        $platforms = $request->input('platforms', []);
        if (is_string($platforms)) {
            $platforms = json_decode($platforms, true) ?? [];
        }

        // Upload image logo
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $ext = $logo->getClientOriginalExtension();
            $filename = Str::slug($request->name) . '_' . time() . '.' . $ext;
            $logoPath = $logo->storeAs('organizations/logo', $filename, 'public');
        }

        // Insert Organization
        $org = Organization::create([
            'name' => $request->name,
            'slug' => $slug,
            'type' => $request->type,
            'description' => $request->description,
            'logo' => $logoPath,
            'website' => $request->website,
            'status' => $request->status,
        ]);

        // Insert Social Accounts
        foreach ($platforms as $account) {
            $org->orgSocialAccounts()->create([
                'platform_id' => $account['platform_id'],
                'username' => $account['username'],
                'url' => $account['url'],
            ]);
        }

        // dd($request->all());

        // Return respons json
        return response()->json([
            'success' => true,
            'message' => 'Organization created successfully!',
            'data' => $org->load([
                'orgSocialAccounts:id,organization_id,platform_id,username,url'
            ])
        ], 201);
    }

    /**
     * Menampilkan detail organisasi berdasarkan ID
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        $organization = Organization::with([
            'vtubers:id,name,slug,status',
            'orgSocialAccounts:id,organization_id,platform_id,username,url',
        ])->withCount('vtubers as talent_count')->find($id);

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get organization details',
            'data' => $organization,
        ], 200);
    }

    /**
     * Update Organization
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        // Find Organization
        $org = Organization::find($id);
        if (!$org) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found',
            ], 404);
        }

        // Generate slug jika user tidak mengisi slug
        $slug = $request->filled('slug') ? $request->slug : Str::slug($request->name);

        // Validator
        $validator = Validator::make(array_merge($request->all(), [
            'slug' => $slug
        ]), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|' . Rule::unique('organizations', 'slug')->ignore($org->id),
            'type' => 'required|in:agency,group',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'website' => 'nullable|url|max:500',
            'status' => 'required|in:active,inactive,liquidated',
        ]);

        // Check Validator errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // Siapkan data
        $data = [
            'name' => $request->name,
            'slug' => $slug,
            'type' => $request->type,
            'description' => $request->description,
            'website' => $request->website,
            'status' => $request->status,
        ];

        // handle image logo
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $ext = $logo->getClientOriginalExtension();
            $filename = Str::slug($request->name) . '_' . time() . '.' . $ext;
            $logoPath = $logo->storeAs('organizations/logo', $filename, 'public');

            if ($org->logo) {
                Storage::disk('public')->delete($org->logo);
            }
            $data['logo'] = $logoPath;
        }

        // Simpan status lama
        $oldStatus = $org->status;

        // Update organization
        $org->update($data);

        // Jika organization berubah menjadi liquidated
        if (
            $oldStatus !== 'liquidated' &&
            $org->status === 'liquidated'
        ) {
            $org->syncVtuberMembership();
        }

        return response()->json([
            'success' => true,
            'message' => 'Organization updated successfully!',
            'data' => $org->load([
                'vtubers:id,name,slug,status',
            ]),
        ], 200);
    }

    /**
     * Delete Organization
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        // Find Organization
        $org = Organization::find($id);
        if (!$org) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found',
            ], 404);
        }

        // Ambil semua VTuber yang menjadi member
        $vtubers = $org->vtubers()->get();

        if ($org->logo) {
            Storage::disk('public')->delete($org->logo);
        }

        $org->delete();

        // Update current affiliation VTuber
        foreach ($vtubers as $vtuber) {
            $vtuber->updateCurrentAffiliation();
        }

        return response()->json([
            'success' => true,
            'message' => 'Organization deleted successfully!'
        ], 200);
    }
}
