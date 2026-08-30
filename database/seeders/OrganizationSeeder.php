<?php

namespace Database\Seeders;

use App\Models\Organization;
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

        foreach ($orgs as $org) {
            $slug = Str::slug($org['name']);

            $org['slug'] = $slug;
            $org['logo'] = 'organizations/logos/' . $slug . '.png';
            $org['created_at'] = Carbon::now();
            $org['updated_at'] = Carbon::now();

            Organization::create($org);
        };
    }
}
