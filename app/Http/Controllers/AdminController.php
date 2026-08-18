<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Platform;
use App\Models\Tag;
use App\Models\Vtuber;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class AdminController extends Controller
{
    // Dashboard Admin and Editor
    public function dashboard()
    {
        // STATISTICS
        $statistics = [
            'total_vtubers' => Vtuber::count(),
            'total_organizations' => Organization::count(),
            'total_platforms' => Platform::count(),
            'total_tags' => Tag::count(),
        ];

        // VTUBER STATUS
        $vtuberStatus = Vtuber::query()->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')->pluck('total', 'status');
        $vtuberStatus = [
            'active' => $vtuberStatus->get('active', 0),
            'inactive' => $vtuberStatus->get('inactive', 0),
            'graduated' => $vtuberStatus->get('graduated', 0),
            'retired' => $vtuberStatus->get('retired', 0),
            'unknown' => $vtuberStatus->get('unknown', 0),
        ];

        // ORGANIZATION STATUS
        $orgStatus = Organization::query()->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')->pluck('total', 'status');
        $orgStatus = [
            'active' => $orgStatus->get('active', 0),
            'inactive' => $orgStatus->get('inactive', 0),
            'liquidated' => $orgStatus->get('liquidated', 0),
        ];

        // RECENT VTUBERS
        $recentVtubers = QueryBuilder::for(Vtuber::class)
            ->select([
                'id',
                'name',
                'slug',
                'avatar',
                'status',
                'updated_at',
            ])->allowedSorts(
                'name',
                'updated_at',
                'status',
            )->defaultSort('-updated_at')
            ->limit(5)
            ->get();

        // RECENT ORGANIZATION
        $recentOrg = QueryBuilder::for(Organization::class)
            ->select([
                'id',
                'name',
                'slug',
                'logo',
                'type',
                'status',
                'updated_at',
            ])->allowedSorts(
                'name',
                'updated_at',
                'status',
            )->defaultSort('-updated_at')
            ->limit(5)
            ->get();

        // DATA ATTENTION
        $dataAttention = [
            'vtubers_without_avatar' => Vtuber::whereNull('avatar')->count(),

            'vtubers_without_social_accounts' => Vtuber::doesntHave(
                'socialAccounts'
            )->count(),

            'organizations_without_logo' => Organization::whereNull('logo')->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Get dashboard data successfully',
            'data' => [
                'statistics' => $statistics,
                'vtuber_status' => $vtuberStatus,
                'organization_status' => $orgStatus,
                'recent_vtubers' => $recentVtubers,
                'recent_organizations' => $recentOrg,
                'data_attention' => $dataAttention,
            ],
        ], 200);
    }
}
