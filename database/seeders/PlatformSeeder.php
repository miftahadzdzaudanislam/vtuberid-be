<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platforms = [
            'YouTube'     => 'https://www.youtube.com',
            'Twitch'      => 'https://www.twitch.tv',
            'X (Twitter)' => 'https://x.com',
            'Instagram'   => 'https://www.instagram.com',
            'TikTok'      => 'https://www.tiktok.com',
            'FaceBook'    => 'https://www/facebook.com',
        ];

        foreach ($platforms as $name => $baseUrl) {
            $slug = Str::slug($name);

            DB::table('platforms')->insert([
                'name' => $name,
                'slug' => $slug,
                'icon' => 'platforms/' . $slug . '.svg',
                'base_url' => $baseUrl,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
