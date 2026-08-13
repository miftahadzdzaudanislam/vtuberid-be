<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VtuberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $firstNames = [
            'Yuzuki',
            'Celeste',
            'Ren',
            'Ivy',
            'Hoshino',
            'Zara',
            'Kaede',
            'Luna',
            'Sable',
            'Nova',
            'Momo',
            'Wren',
            'Kirin',
            'Iris',
            'Aoi',
        ];
        $lastNames = [
            'Amane',
            'Fairwind',
            'Kurogami',
            'Nightshade',
            'Mikan',
            'Voxel',
            'Shirotsuki',
            'Everhart',
            'Kitsune',
            'Aeternum',
            'Sakuraba',
            'Ashfall',
            'Tsukiyo',
            'Wavecrest',
            'Tamashiro',
        ];

        $genders      = ['male', 'female'];
        $affiliations = array_merge(
            array_fill(0, 4, 'organization'),
            array_fill(0, 1, 'independent'),
        );
        $statuses = ['active', 'inactive', 'hiatus', 'graduated', 'retired', 'unknown'];

        $names = [];
        while (count($names) < 10) {
            $name = $faker->randomElement($firstNames) . ' ' . $faker->randomElement($lastNames);
            $names[$name] = true;
        }

        foreach (array_keys($names) as $name) {
            DB::table('vtubers')->insert([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $faker->realText(180),
                'gender' => $faker->randomElement($genders),
                'debut_date' => $faker->dateTimeBetween('-4 years', '-1 month')->format('Y-m-d'),
                'birthday' => $faker->dateTimeBetween('-30 years', '-18 years')->format('m-d'),
                'height' => $faker->numberBetween(100, 300),
                'status' => $faker->randomElement($statuses),
                'current_affiliation' => $faker->randomElement($affiliations),
                'avatar' => 'vtubers/avatars/' . Str::slug($name) . '.png',
                'banner' => 'vtubers/banners/' . Str::slug($name) . '.png',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
