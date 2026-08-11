<?php

namespace Database\Seeders;

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

        $types = ['agency', 'group'];
        $statuses = ['active', 'inactive', 'liquidated'];
        $firstName = ['Cozy', 'Live', 'Dream', 'Virtual', 'Creative'];
        $lastName = ['Studio', 'Entertainment', 'Collective', 'Productions', 'Media'];
        $slugs = [];

        for ($i=0; $i < 3; $i++) { 
            $name = $faker->randomElement($firstName).' '.$faker->randomElement($lastName);
            $slug = Str::slug($name);

            if (in_array($slug, $slugs)) {
                $slug .= '-'.Str::random(4);
            }
            $slugs[] = $slug;

            DB::table('organizations')->insert([
                'name' => $name,
                'slug' => $slug,
                'type' => $faker->randomElement($types),
                'description' => $faker->realText(150),
                'logo' => 'organizations/'. $slug. '.png',
                'website' => $faker->url(),
                'status' => $faker->randomElement($statuses),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
