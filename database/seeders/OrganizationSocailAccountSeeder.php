<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class OrganizationSocailAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $organizations = DB::table('organizations')->get();
        $platforms = DB::table('platforms')->get();

        if ($organizations->isEmpty() || $platforms->isEmpty()) {
            $this->command->warn('Skip SocialAccountSeeder: organizations/platforms masih kosong.');

            return;
        }

        $handlePlatforms = ['youtube', 'tiktok'];

        foreach ($organizations as $organization) {
            $platformCount = min($platforms->count(), $faker->numberBetween(1, 3));
            $selectedPlatforms = $platforms->random($platformCount);

            foreach ($selectedPlatforms as $platform) {
                $username = $organization->slug . $faker->numberBetween(1, 99);

                if (in_array($platform->slug, $handlePlatforms)) {
                    $username = '@' . $username;
                }

                DB::table('organization_social_accounts')->insert([
                    'organization_id' => $organization->id,
                    'platform_id' => $platform->id,
                    'username' => $username,
                    'url' => rtrim($platform->base_url, '/') . '/' . $username,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}
