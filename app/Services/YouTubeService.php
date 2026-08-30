<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class YouTubeService
{
    // Base URL Youtube Data API v3
    protected string $baseUrl = 'https://www.googleapis.com/youtube/v3';

    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.youtube.api_key');
    }

    public function getChannelByUsername(string $username): ?array
    {
        $response = Http::get("{$this->baseUrl}/channels", [
            'part' => 'snippet',
            'forHandle' => '@' . ltrim($username, '@'),
            'key' => $this->apiKey,
        ]);

        if ($response->failed()) {
            return null;
        }

        return $response->json('items.0');
    }

    /**
     * Get YouTube channel avatar
     * @param string $channelId
     * @return string|null
     */
    public function getChannelAvatar(string $channelId): ?string
    {
        return Cache::remember(
            // simpan selama 12 jam
            "youtube:channel:{$channelId}:avatar",
            now()->addHours(12),
            function () use ($channelId) {
                $response = Http::get("{$this->baseUrl}/channels", [
                    'part' => 'snippet',
                    'id' => $channelId,
                    'key' => $this->apiKey
                ]);

                if ($response->failed()) {
                    return null;
                }

                $channel = $response->json('items.0');
                if (!$channel) {
                    return null;
                }

                return data_get(
                    $channel,
                    'snippet.thumbnails.high.url',
                    data_get(
                        $channel,
                        'snippet.thumbnails.medium.url',
                        data_get(
                            $channel,
                            'snippet.thumbnails.default.url'
                        )
                    )
                );
            }
        );
    }

    /**
     * Get currently live video from channel
     * @param string $channelId
     * @return array|null
     */
    public function getLive(string $channelId): ?array
    {
        $response = Http::get("{$this->baseUrl}/search", [
            'part' => 'snippet',
            'channelId' => $channelId,
            'eventType' => 'live',
            'type' => 'video',
            'maxResults' => 1,
            'key' => $this->apiKey,
        ]);

        if ($response->failed()) {
            return null;
        }

        $item = $response->json('items.0');

        if (!$item) {
            return null;
        }

        return [
            'video_id' => data_get($item, 'id.videoId'),
            'title' => data_get($item, 'snippet.title'),

            'thumbnail' => data_get(
                $item,
                'snippet.thumbnails.maxres.url',
                data_get($item, 'snippet.thumbnails.high.url')
            ),

            'channel_id' => data_get($item, 'snippet.channelId'),
            'channel_title' => data_get($item, 'snippet.channelTitle'),
        ];
    }

    /**
     * Get video detail (Stream info)
     * @param string $videoId
     * @return array|null
     */
    public function getVideo(string $videoId): ?array
    {
        $response = Http::get("{$this->baseUrl}/videos", [
            'part' => 'snippet,liveStreamingDetails',
            'id' => $videoId,
            'key' => $this->apiKey,
        ]);

        if ($response->failed()) {
            return null;
        }

        return $response->json('items.0');
    }

    /**
     * Get complete live data from channel
     * @param string $channelId
     * @return array|null
     */
    public function getLiveData(string $channelId): ?array
    {
        $live = $this->getLive($channelId);

        if (!$live) {
            return null;
        }

        $videoId = $live['video_id'] ?? null;

        if (!$videoId) {
            return null;
        }

        $video = $this->getVideo($videoId);

        if (!$video) {
            return null;
        }

        return [
            'video_id' => $videoId,
            'title' => data_get($video, 'snippet.title'),

            'thumbnail' => data_get(
                $video,
                'snippet.thumbnails.maxres.url',
                data_get($video, 'snippet.thumbnails.high.url')
            ),

            'url' => "https://www.youtube.com/watch?v={$videoId}",

            'viewer_count' => data_get($video, 'liveStreamingDetails.concurrentViewers'),
            'started_at' => data_get($video, 'liveStreamingDetails.actualStartTime'),
            'scheduled_at' => data_get($video, 'liveStreamingDetails.scheduledStartTime'),
        ];
    }

    public function getAvatarByUsername(string $username): ?string
    {
        $channel = $this->getChannelByUsername($username);
        if (!$channel) {
            return null;
        }

        $channelId = $channel['id'] ?? null;
        if (!$channelId) {
            return null;
        }

        return $this->getChannelAvatar($channelId);
    }

    public function getLiveDataByUsername(string $username): ?array
    {
        $channel = $this->getChannelByUsername($username);
        if (!$channel) {
            return null;
        }

        $channelId = $channel['id'] ?? null;
        if (!$channelId) {
            return null;
        }

        return $this->getLiveData($channelId);
    }
}
