<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Services\SocialAccountService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $socialAccountService = app(SocialAccountService::class);

        // Livium, re:memories, echianaselia

        $orgs = [
            [
                'name' => 'Project Livium',
                'yt_username' => 'ProjectLIVIUM',
                'type' => 'agency',
                'description' => $faker->realText(150),
                'website' => $faker->url(),
                'status' => 'active',
            ],
            [
                'name' => 'Re:memories',
                'yt_username' => '@rememoriesid',
                'type' => 'agency',
                'description' => $faker->realText(150),
                'website' => $faker->url(),
                'status' => 'active',
            ],
            [
                'name' => 'Echianaselia',
                'type' => 'group',
                'description' => $faker->realText(150),
                'website' => $faker->url(),
                'status' => 'active',
            ],
            [
                'name' => 'EOS',
                'yt_username' => '@EonOfStars',
                'type' => 'group',
                'description' => $faker->realText(150),
                'website' => $faker->url(),
                'status' => 'liquidated',
            ],
        ];

        foreach ($orgs as $data) {
            $slug = Str::slug($data['name']);

            $data['slug'] = $slug;
            $data['logo'] = 'organizations/logos/' . $slug . '.png';
            $data['created_at'] = Carbon::now();
            $data['updated_at'] = Carbon::now();

            $org = Organization::create($data);

            if ($org->yt_username) {
                $socialAccountService->syncOrgYoutube($org);
            }
        };
    }
}
