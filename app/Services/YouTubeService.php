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

    /**
     * Get Channel by Username
     * @param string $username
     */
    public function getChannelByUsername(string $username): ?array
    {
        $response = Http::get("{$this->baseUrl}/channels", [
            'part' => 'snippet,statistics',
            'forHandle' => '@' . ltrim($username, '@'),
            'key' => $this->apiKey,
        ]);

        if ($response->failed()) {
            return null;
        }

        return $response->json('items.0');
    }

    /**
     * Get Data Channel
     * @param string $username
     * @return array{avatar: mixed, "channel_id": mixed, name: mixed, "subscriber_count": mixed, "video_count": mixed, "view_count": mixed|mixed|null}
     */
    public function getChannelData(string $username): ?array
    {
        return Cache::remember(
            // simpan selama 12 jam
            "youtube:channel:{$username}",
            now()->addHours(12),
            function () use ($username) {
                $channel = $this->getChannelByUsername($username);

                if (!$channel) {
                    return null;
                }

                return [
                    'channel_id' => data_get($channel, 'id'),
                    'name' => data_get($channel, 'snippet.title'),
                    'avatar' => data_get(
                        $channel,
                        'snippet.thumbnails.high.url',
                        data_get(
                            $channel,
                            'snippet.thumbnails.medium.url',
                            data_get($channel, 'snippet.thumbnails.default.url')
                        )
                    ),

                    'subscriber_count' => data_get($channel, 'statistics.subscriberCount'),
                    'video_count' => data_get($channel, 'statistics.videoCount'),
                    'view_count' => data_get($channel, 'statistics.viewCount'),
                ];
            }
        );
    }

    /**
     * Get Avatar by Username
     * @param string $username
     * @return string|null
     */
    public function getAvatarByUsername(string $username): ?string
    {
        return data_get(
            $this->getChannelData($username),
            'avatar'
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

    /**
     * Get Live data by Username
     * @param string $username
     * @return array|null
     */
    public function getLiveDataByUsername(string $username): ?array
    {
        $channel = $this->getChannelData($username);
        if (!$channel) {
            return null;
        }

        $channelId = $channel['channel_id'] ?? null;
        if (!$channelId) {
            return null;
        }

        return $this->getLiveData($channelId);
    }
}
