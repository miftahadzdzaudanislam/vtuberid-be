<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class SocialAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $vtubers = DB::table('vtubers')->get();
        $platforms = DB::table('platforms')->whereNot('slug', 'youtube')->get();

        if ($vtubers->isEmpty() || $platforms->isEmpty()) {
            $this->command->warn('Skip SocialAccountSeeder: vtubers/platforms masih kosong.');
            
            return;
        }

        $handlePlatforms = ['youtube', 'tiktok'];

        foreach ($vtubers as $vtuber) {
            $platformCount = min($platforms->count(), $faker->numberBetween(1, 3));
            $selectedPlatforms = $platforms->random($platformCount);

            foreach ($selectedPlatforms as $platform) {
                $username = $vtuber->slug . $faker->numberBetween(1, 99);

                if (in_array($platform->slug, $handlePlatforms)) {
                    $username = '@' . $username;
                }

                DB::table('social_accounts')->insert([
                    'vtuber_id' => $vtuber->id,
                    'platform_id' => $platform->id,
                    'username' => $username,
                    'url' => rtrim($platform->base_url, '/') . '/' . $username,
                    'followers' => $faker->numberBetween(1000, 500000),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}
