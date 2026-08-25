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

        if (!$vtuber->youtube_channel_id) {
            return response()->json([
                'success' => false,
                'message' => 'YouTube channel not found',
            ], 404);
        }

        // Cek apakah channel live
        $live = $youtube->getLiveData(
            $vtuber->youtube_channel_id
        );

        if (!$live) {
            return response()->json([
                'success' => true,
                'message' => 'VTuber is not currently live',
                'data' => null,
            ]);
        }

        // Gunakan avatar youtube, database sebagai fallback
        $avatar = $youtube->getChannelAvatar($vtuber->youtube_channel_id) ?? $vtuber->avatar;

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
        $vtubers = Vtuber::whereNotNull('youtube_channel_id')
            ->where('youtube_channel_id', '!=', '')
            ->get();

        $liveVtubers = [];

        foreach ($vtubers as $vtuber) {
            // Cek live vtuber
            $live = $youtube->getLiveData(
                $vtuber->youtube_channel_id
            );

            // lewati jika tidak live
            if (!$live) {
                continue;
            }

            // Gunakan avatar youtube, database sebagai fallback
            $avatar = $youtube->getChannelAvatar($vtuber->youtube_channel_id) ?? $vtuber->avatar;

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
