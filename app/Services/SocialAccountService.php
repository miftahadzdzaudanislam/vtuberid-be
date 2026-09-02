<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Vtuber;
use Illuminate\Support\Facades\DB;

class SocialAccountService
{
    public function __construct(
        protected YouTubeService $youtube
    ) {}

    public function syncVtuberYoutube(Vtuber $vtuber)
    {
        if (!$vtuber->yt_username) {
            return null;
        }

        $platformId = DB::table('platforms')->where('slug', 'youtube')->value('id');
        if (!$platformId) {
            return null;
        }

        $username = ltrim($vtuber->yt_username, '@');
        $youtube = $this->youtube->getChannelData($username);
        if (!$youtube) {
            return null;
        }

        $vtuber->socialAccounts()->updateOrCreate(
            ['platform_id' => $platformId],
            [
                'username' => $username,
                'url' => "https://www.youtube.com/@{$username}",
                'followers' => $youtube['subscriber_count'] ?? null
            ],
        );
    }

    public function syncOrgYoutube(Organization $org)
    {
        if (!$org->yt_username) {
            return null;
        }

        $platformId = DB::table('platforms')->where('slug', 'youtube')->value('id');
        if (!$platformId) {
            return null;
        }

        $username = ltrim($org->yt_username, '@');
        $youtube = $this->youtube->getChannelData($username);
        if (!$youtube) {
            return null;
        }

        $org->orgSocialAccounts()->updateOrCreate(
            ['platform_id' => $platformId],
            [
                'username' => $username,
                'url' => "https://www.youtube.com/@{$username}"
            ],
        );
    }

    public function removeVtuberYoutube(Vtuber $vtuber): void
    {
        $platformId = DB::table('platforms')->where('slug', 'youtube')->value('id');

        if (!$platformId) {
            return;
        }

        $vtuber->socialAccounts()->where('platform_id', $platformId)->delete();
    }

    public function removeOrgYoutube(Organization $org): void
    {
        $platformId = DB::table('platforms')->where('slug', 'youtube')->value('id');

        if (!$platformId) {
            return;
        }

        $org->orgSocialAccounts()->where('platform_id', $platformId)->delete();
    }
}
