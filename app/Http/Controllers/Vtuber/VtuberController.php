<?php

namespace App\Http\Controllers\Vtuber;

use App\Http\Controllers\Controller;
use App\Models\Vtuber;
use App\Services\SocialAccountService;
use App\Services\YouTubeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class VtuberController extends Controller
{
    /**
     * Menampilkan daftar vtuber
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function daftarVtuber(Request $request, YouTubeService $youtube)
    {
        $limit = $request->input('limit', 50);

        $vtubers = QueryBuilder::for(Vtuber::class)
            ->select([
                'id',
                'name',
                'slug',
                'yt_username',
                'avatar',
                'status',
                'current_affiliation'
            ])
            ->allowedFilters(
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('name', 'like', "%{$value}%");
                    $query->orWhere('slug', 'like', "%{$value}%");
                }),
                AllowedFilter::exact('status'),
            )->with([
                'organizations' => function ($query) {
                    $query->select(
                        'organizations.id',
                        'organizations.name',
                        'organizations.slug',
                    )->wherePivot('status', 'active');
                },
                'tags:id,name,slug',
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

        // Ambil avatar dari YouTube
        $vtubers->getCollection()->transform(function ($vtuber) use ($youtube) {
            if ($vtuber->yt_username) {
                $youtubeAvatar = $youtube->getAvatarByUsername($vtuber->yt_username);

                // Gunakan avatar database jika YouTube tidak ditemukan 
                $vtuber->avatar = $youtubeAvatar ?? $vtuber->avatar;
            }

            $vtuber->organizations->each->makeHidden('pivot');

            return $vtuber;
        });

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
     * Menampilkan detail vtuber berdasarkan slug
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function detailVtuber(string $slug, YouTubeService $youtube)
    {
        $vtuber = Vtuber::with([
            'organizations:id,name,slug,yt_username,type,logo',
            'tags:id,name,slug',
            'socialAccounts:id,vtuber_id,platform_id,username,url,followers',
            'socialAccounts.platform:id,name,icon'
        ])->where('slug', $slug)->first();

        if (!$vtuber) {
            return response()->json([
                'success' => false,
                'message' => 'Vtuber not found'
            ], 404);
        }

        // Ambil avatar dari YouTube
        if ($vtuber->yt_username) {
            $youtubeAvatar = $youtube->getAvatarByUsername(
                $vtuber->yt_username
            );

            $vtuber->avatar = $youtubeAvatar ?? $vtuber->avatar;
        }

        // Ambil logo organisasi dari YouTube
        $vtuber->organizations->each(function ($organization) use ($youtube) {
            if ($organization->yt_username) {
                $youtubeLogo = $youtube->getAvatarByUsername($organization->yt_username);

                $organization->logo = $youtubeLogo ?? $organization->logo;
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Get vtuber details',
            'data' => $vtuber,
        ], 200);
    }

    /**
     * Get Vtuber Events
     * @param Request $request
     * @param YouTubeService $youtube
     * @return \Illuminate\Http\JsonResponse
     */
    public function vtuberEvents(Request $request, YouTubeService $youtube)
    {
        $month = (int) $request->input('month', Carbon::now()->month);

        // Validasi bulan 1 - 12
        if ($month < 1 || $month > 12) {
            return response()->json([
                'success' => false,
                'message' => 'Month must be between 1 and 12.',
            ], 422);
        }

        $year = Carbon::now()->year;

        // Birthday
        $birthday = QueryBuilder::for(Vtuber::class)
            ->select([
                'id',
                'name',
                'slug',
                'yt_username',
                'avatar',
                'birthday',
                'status',
            ])->whereNotNull('birthday')
            ->whereNotIn('status', ['retired', 'graduated'])
            ->get()
            ->filter(function (Vtuber $vtuber) use ($month) {
                return (int) substr($vtuber->birthday, 0, 2) === $month;
            })->map(function (Vtuber $vtuber) use ($year) {
                $eventDate = Carbon::createFromFormat(
                    'Y-m-d',
                    $year . '-' . $vtuber->birthday
                );

                return [
                    'id' => $vtuber->id,
                    'name' => $vtuber->name,
                    'slug' => $vtuber->slug,
                    'yt_username' => $vtuber->yt_username,
                    'avatar' => $vtuber->avatar,
                    'status' => $vtuber->status,
                    'type' => 'birthday',
                    'event_date' => $eventDate->toDateString(),
                ];
            })->values();

        // Debut Anniv
        $debutAnniv = QueryBuilder::for(Vtuber::class)
            ->select([
                'id',
                'name',
                'slug',
                'yt_username',
                'avatar',
                'debut_date',
                'status',
            ])->whereNotNull('debut_date')
            ->whereNotIn('status', ['retired', 'graduated'])
            ->get()
            ->map(function (Vtuber $vtuber) use ($year) {
                $debutDate = Carbon::parse($vtuber->debut_date);
                $eventDate = $debutDate->copy()->year($year);

                return [
                    'id' => $vtuber->id,
                    'name' => $vtuber->name,
                    'slug' => $vtuber->slug,
                    'yt_username' => $vtuber->yt_username,
                    'avatar' => $vtuber->avatar,
                    'status' => $vtuber->status,
                    'type' => 'debut_anniversary',
                    'event_date' => $eventDate->toDateString(),
                    'anniversary_year' =>
                    $year - $debutDate->year,
                ];
            })->filter(function ($event) use ($month) {
                return Carbon::parse(
                    $event['event_date']
                )->month === $month;
            })
            ->values();


        // Graduation day
        $graduationDay = QueryBuilder::for(Vtuber::class)
            ->select([
                'id',
                'name',
                'slug',
                'yt_username',
                'avatar',
                'graduate_date',
                'status',
            ])->whereNotNull('graduate_date')
            ->whereNotIn('status', ['retired', 'graduated'])
            ->whereMonth('graduate_date', $month)
            ->get()
            ->map(function (Vtuber $vtuber) use ($year) {
                $eventDate = Carbon::parse($vtuber->graduate_date)->copy()->year($year);

                return [
                    'id' => $vtuber->id,
                    'name' => $vtuber->name,
                    'slug' => $vtuber->slug,
                    'yt_username' => $vtuber->yt_username,
                    'avatar' => $vtuber->avatar,
                    'status' => $vtuber->status,
                    'type' => 'graduation',
                    'event_date' => $eventDate->toDateString(),
                ];
            })->values();

        // Gabungkan event
        $events = collect()
            ->merge($birthday)
            ->merge($debutAnniv)
            ->merge($graduationDay)
            ->sortBy([
                ['event_date', 'asc'],
                ['name', 'asc']
            ])
            ->values();

        $events = $events->map(function ($event) use ($youtube) {
            if ($event['yt_username']) {
                $youtubeAvatar = $youtube->getAvatarByUsername($event['yt_username']);

                // Gunakan avatar database sebagai fallback 
                $event['avatar'] = $youtubeAvatar ?? $event['avatar'];
            }

            return $event;
        });

        // Group berdasarkan tanggal
        $eventsByDate = $events
            ->groupBy('event_date')
            ->map(function ($items, $date) {
                return [
                    'date' => $date,
                    'events' => $items->values(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Get vtuber events',
            'data' => [
                'year' => $year,
                'month' => $month,
                'events' => $eventsByDate,
            ],
        ], 200);
    }

    /**
     * Menampilkan data vtuber untuk admin
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, YouTubeService $youtube)
    {
        $limit = $request->input('limit', 50);

        $vtubers = QueryBuilder::for(Vtuber::class)
            ->select([
                'id',
                'name',
                'slug',
                'yt_username',
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
                'organizations' => function ($query) {
                    $query->select(
                        'organizations.id',
                        'organizations.name',
                        'organizations.slug',
                    )->wherePivot('status', 'active');
                },
                'tags:id,name,slug',
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

        $vtubers->getCollection()->transform(function ($vtuber) use ($youtube) {
            if ($vtuber->yt_username) {
                $youtubeAvatar = $youtube->getAvatarByUsername($vtuber->yt_username);

                // YouTube sebagai avatar utama, database sebagai fallback 
                $vtuber->avatar = $youtubeAvatar ?? $vtuber->avatar;
            }

            // Jangan kirim pivot ke frontend 
            $vtuber->organizations->each->makeHidden('pivot');
            return $vtuber;
        });

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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, SocialAccountService $socialAccount)
    {
        // Generate slug jika user tidak mengisi slug
        $slug = $request->filled('slug') ? $request->slug : Str::slug($request->name);

        // Validator
        $validator = Validator::make(array_merge($request->all(), [
            'slug' => $slug
        ]), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:vtubers,slug',
            'yt_username' => 'nullable|string|max:255|unique:vtubers,yt_username',
            'description' => 'nullable|string',
            'gender' => 'required|in:male,female',
            'debut_date' => 'nullable|date',
            'birthday' => 'nullable|date_format:m-d',
            'graduate_date' => 'nullable|date',
            'height' => 'nullable|integer|min:1|max:300',
            'status' => 'required|in:active,inactive,hiatus,graduated,retired,unknown',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            'tag_ids' => 'nullable|string',
        ]);

        // Check Validator errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // Get data
        $tagIds = $request->input('tag_ids', []);

        // Convert string to array
        if (is_string($tagIds)) {
            $tagIds = array_filter(
                array_map('intval', explode(',', $tagIds))
            );
        }

        $avatarPath = null;
        $bannerPath = null;

        // Upload image avatar & banner
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $ext = $avatar->getClientOriginalExtension();
            $filename = Str::slug($request->name) . '_' . time() . '.' . $ext;
            $avatarPath = $avatar->storeAs('vtubers/avatar', $filename, 'public');
        }

        if ($request->hasFile('banner')) {
            $banner = $request->file('banner');
            $ext = $banner->getClientOriginalExtension();
            $filename = Str::slug($request->name) . '_banner_' . time() . '.' . $ext;
            $bannerPath = $banner->storeAs('vtubers/banner', $filename, 'public');
        }

        // Insert Vtuber
        $vtuber = Vtuber::create([
            'name' => $request->name,
            'slug' => $slug,
            'yt_username' => $request->yt_username,
            'description' => $request->description,
            'gender' => $request->gender,
            'debut_date' => $request->debut_date,
            'birthday' => $request->birthday,
            'graduate_date' => $request->graduate_date,
            'height' => $request->height,
            'status' => $request->status,
            'current_affiliation' => 'independent',
            'avatar' => $avatarPath,
            'banner' => $bannerPath,
        ]);

        // Insert Tags
        if (!empty($tagIds)) {
            $vtuber->tags()->sync($tagIds);
        }

        // Buat Social Account YouTube otomatis
        if ($vtuber->yt_username) {
            $socialAccount->syncVtuberYoutube($vtuber);
        }

        // Return respons json
        return response()->json([
            'success' => true,
            'message' => 'Vtuber created successfully!',
            'data' => $vtuber->load([
                'tags:id,name,slug',
                'socialAccounts:id,vtuber_id,platform_id,username,url,followers',
                'socialAccounts.platform:id,name,icon'
            ])
        ], 201);
    }

    /**
     * Menampilkan detail vtuber berdasarkan ID
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id, YouTubeService $youtube)
    {
        // Search Vtuber
        $vtuber = Vtuber::with([
            'organizations:id,name,slug,yt_username,type,logo',
            'tags:id,name,slug',
            'socialAccounts:id,vtuber_id,platform_id,username,url,followers',
            'socialAccounts.platform:id,name'
        ])->find($id);

        if (!$vtuber) {
            return response()->json([
                'success' => false,
                'message' => 'Vtuber not found'
            ], 404);
        }

        // Ambil avatar dari YouTube
        if ($vtuber->yt_username) {
            $youtubeAvatar = $youtube->getAvatarByUsername(
                $vtuber->yt_username
            );

            $vtuber->avatar = $youtubeAvatar ?? $vtuber->avatar;
        }

        // Ambil logo organisasi dari YouTube
        $vtuber->organizations->each(function ($organization) use ($youtube) {
            if ($organization->yt_username) {
                $youtubeLogo = $youtube->getAvatarByUsername($organization->yt_username);

                $organization->logo = $youtubeLogo ?? $organization->logo;
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Get vtuber details',
            'data' => $vtuber,
        ], 200);
    }

    /**
     * Update Vtuber
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id, SocialAccountService $socialAccount)
    {
        // Find vtuber
        $vtuber = Vtuber::find($id);
        if (!$vtuber) {
            return response()->json([
                'success' => false,
                'message' => 'Vtuber not found',
            ], 404);
        }

        // Generate slug jika user tidak mengisi slug
        $slug = $request->filled('slug') ? $request->slug : Str::slug($request->name);

        // Validator
        $validator = Validator::make(array_merge($request->all(), [
            'slug' => $slug
        ]), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|' . Rule::unique('vtubers', 'slug')->ignore($vtuber->id),
            'yt_username' => 'nullable|string|max:255|' . Rule::unique('vtubers', 'yt_username')->ignore($vtuber->id),
            'description' => 'nullable|string',
            'gender' => 'required|in:male,female',
            'debut_date' => 'nullable|date',
            'birthday' => 'nullable|date_format:m-d',
            'graduate_date' => 'nullable|date',
            'height' => 'nullable|integer|min:1|max:300',
            'status' => 'required|in:active,inactive,hiatus,graduated,retired,unknown',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            'tag_ids' => 'nullable|string',
        ]);

        // Check Validator errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // Get data
        $tagIds = $request->input('tag_ids', []);

        // Convert string to array
        if (is_string($tagIds)) {
            $tagIds = array_filter(
                array_map('intval', explode(',', $tagIds))
            );
        }

        // Simpan kondisi yt_username sebelum update
        $oldYoutubeUsername = $vtuber->yt_username;

        // siapkan data yang ingin di update
        $data = [
            'name' => $request->name,
            'slug' => $slug,
            'yt_username' => $request->filled('yt_username') ? ltrim($request->yt_username, '@') : null,
            'description' => $request->description,
            'gender' => $request->gender,
            'debut_date' => $request->debut_date,
            'birthday' => $request->birthday,
            'graduate_date' => $request->graduate_date,
            'height' => $request->height,
            'status' => $request->status,
        ];

        // handle image avatar & banner
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $ext = $avatar->getClientOriginalExtension();
            $filename = Str::slug($request->name) . '_' . time() . '.' . $ext;
            $avatarPath = $avatar->storeAs('vtubers/avatar', $filename, 'public');

            if ($vtuber->avatar) {
                Storage::disk('public')->delete($vtuber->avatar);
            }
            $data['avatar'] = $avatarPath;
        }

        if ($request->hasFile('banner')) {
            $banner = $request->file('banner');
            $ext = $banner->getClientOriginalExtension();
            $filename = Str::slug($request->name) . '_banner_' . time() . '.' . $ext;
            $bannerPath = $banner->storeAs('vtubers/banner', $filename, 'public');

            if ($vtuber->banner) {
                Storage::disk('public')->delete($vtuber->banner);
            }
            $data['banner'] = $bannerPath;
        }

        // Update vtuber
        $vtuber->update($data);

        // Sync and update status
        $vtuber->syncOrganizationStatus();
        $vtuber->updateCurrentAffiliation();

        // Update Tags
        if ($request->has('tag_ids')) {
            $vtuber->tags()->sync($tagIds);
        }

        // Update or delete yt username
        if ($vtuber->yt_username) {
            $socialAccount->syncVtuberYoutube($vtuber);
        } elseif ($oldYoutubeUsername) {
            $socialAccount->removeVtuberYoutube($vtuber);
        }

        $vtuber->refresh();

        $vtuber->load([
            'organizations:id,name,slug,type,logo',
            'tags:id,name,slug',
            'socialAccounts:id,vtuber_id,platform_id,username,url,followers',
            'socialAccounts.platform:id,name',
        ]);

        // Return respons json
        return response()->json([
            'success' => true,
            'message' => 'Vtuber updated successfully!',
            'data' => $vtuber,
        ], 200);
    }

    /**
     * Delete Vtuber
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        // Find vtuber
        $vtuber = Vtuber::find($id);
        if (!$vtuber) {
            return response()->json([
                'success' => false,
                'message' => 'Vtuber not found',
            ], 404);
        }

        if ($vtuber->avatar) {
            Storage::disk('public')->delete($vtuber->avatar);
        }
        if ($vtuber->banner) {
            Storage::disk('public')->delete($vtuber->banner);
        }

        $vtuber->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vtuber deleted successfully!'
        ], 200);
    }
}
