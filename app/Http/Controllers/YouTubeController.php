<?php

namespace App\Http\Controllers;

use App\Models\Vtuber;
use App\Services\YouTubeService;

class YouTubeController extends Controller
{
    /**
     * Get Detail Live
     * @param string $slug
     * @param YouTubeService $youtube
     * @return \Illuminate\Http\JsonResponse
     */
    public function live(string $slug, YouTubeService $youtube)
    {
        $vtuber = Vtuber::where('slug', $slug)->first();
        if (!$vtuber) {
            return response()->json([
                'success' => false,
                'message' => 'VTuber not found',
            ], 404);
        }

        if (!$vtuber->yt_username) {
            return response()->json([
                'success' => false,
                'message' => 'YouTube channel not found',
            ], 404);
        }

        // Cek apakah channel live
        $live = $youtube->getLiveDataByUsername(
            $vtuber->yt_username
        );

        if (!$live) {
            return response()->json([
                'success' => true,
                'message' => 'VTuber is not currently live',
                'data' => null,
            ]);
        }

        // Gunakan avatar youtube, database sebagai fallback
        $avatar = $youtube->getAvatarByUsername($vtuber->yt_username) ?? $vtuber->avatar;

        return response()->json([
            'success' => true,
            'message' => 'VTuber is currently live',
            'data' => [
                'vtuber' => [
                    'id' => $vtuber->id,
                    'name' => $vtuber->name,
                    'slug' => $vtuber->slug,
                    'avatar' => $avatar,
                ],
                'youtube' => $live,
            ],
        ]);
    }

    /**
     * Get Vtubers live
     * @param YouTubeService $youtube
     * @return \Illuminate\Http\JsonResponse
     */
    public function liveVtubers(YouTubeService $youtube)
    {
        // Hanya ambil VTuber yang memiliki YouTube channel ID
        $vtubers = Vtuber::whereNotNull('yt_username')
            ->where('yt_username', '!=', '')
            ->get();

        $liveVtubers = [];

        foreach ($vtubers as $vtuber) {
            // Cek live vtuber
            $live = $youtube->getLiveDataByUsername(
                $vtuber->yt_username
            );

            // lewati jika tidak live
            if (!$live) {
                continue;
            }

            // Gunakan avatar youtube, database sebagai fallback
            $avatar = $youtube->getAvatarByUsername($vtuber->yt_username) ?? $vtuber->avatar;

            $liveVtubers[] = [
                'id' => $vtuber->id,
                'name' => $vtuber->name,
                'slug' => $vtuber->slug,
                'avatar' => $avatar,
                'youtube' => $live,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Get live VTubers successfully',
            'live_count' => count($liveVtubers),
            'data' => $liveVtubers,
        ]);
    }
}
