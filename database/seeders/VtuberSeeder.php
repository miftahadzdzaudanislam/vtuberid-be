<?php

namespace Database\Seeders;

use App\Models\Vtuber;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VtuberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Kanna, miti, naomi, echi, cecil, leo, silvia, indira, dina, souta
        $vtubers = [
            [
                'name' => 'Kanna Tamachi',
                'yt_username' => '@kanna.tamachi',
                'description' => 'Deskripsi Kanna Tamachi',
                'gender' => 'female',
                'status' => 'inactive',
                'current_affiliation' => 'independent',
            ],
            [
                'name' => 'Mythia Batford',
                'yt_username' => 'mythiabatfordch',
                'description' => 'Deskripsi Mythia Batford',
                'birthday' => '2005-08-28',
                'gender' => 'female',
                'status' => 'active',
                'current_affiliation' => 'independent',
            ],
            // [
            //     'name' => 'Noemi Hestia',
            //     'youtube_channel_id' => 'UCA7tDob1IQiKWXnGktjPKQA',
            //     'description' => 'Deskripsi Noemi Hestia',
            //     'gender' => 'female',
            //     'status' => 'active',
            //     'current_affiliation' => 'independent',
            // ],
            [
                'name' => 'Noemi Hestia',
                'yt_username' => '@DeltorielVT',
                'description' => 'Deskripsi Noemi Hestia',
                'gender' => 'female',
                'status' => 'active',
                'current_affiliation' => 'independent',
            ],
            [
                'name' => 'Souta Izumi',
                'yt_username' => 'xSoutaa',
                'description' => 'Deskripsi Souta Izumi',
                'gender' => 'male',
                'graduate_date' => '2025-01-01',
                'status' => 'retired',
                'current_affiliation' => 'independent',
            ],
            [
                'name' => 'Elaine Celestia',
                'yt_username' => '@ElaineCelestia',
                'description' => 'Deskripsi Elaine Celestia',
                'gender' => 'female',
                'status' => 'active',
                'current_affiliation' => 'organization',
            ],
            [
                'name' => 'Cecilia Liberia',
                'yt_username' => 'CeciliaLieberia',
                'description' => 'Deskripsi Cecilia Liberia',
                'gender' => 'female',
                'status' => 'active',
                'current_affiliation' => 'organization',
            ],
            [
                'name' => 'Leo Axorus',
                'yt_username' => '@LeoAxenos',
                'description' => 'Deskripsi Leo Axorus',
                'gender' => 'male',
                'status' => 'graduated',
                'current_affiliation' => 'independent',
            ],
            [
                'name' => 'Silvia Valleria',
                'yt_username' => '@SilviaValleria',
                'description' => 'Deskripsi Silvia Valleria',
                'gender' => 'female',
                'status' => 'active',
                'current_affiliation' => 'organization',
            ],
            [
                'name' => 'Indira Naylarissa',
                'yt_username' => 'IndiraNaylarissa',
                'description' => 'Deskripsi Indira Naylarissa',
                'gender' => 'female',
                'status' => 'active',
                'current_affiliation' => 'organization',
            ],
            [
                'name' => 'Dina Monstatera',
                'yt_username' => '@DinaMostarterra',
                'description' => 'Deskripsi Dina Monstatera',
                'gender' => 'female',
                'status' => 'graduated',
                'current_affiliation' => 'independent',
            ],
        ];

        foreach ($vtubers as $vtuber) {
            $slug = Str::slug($vtuber['name']);

            $vtuber['slug'] = $slug;
            $vtuber['debut_date'] = $faker->dateTimeBetween('-4 years', '-1 month')->format('Y-m-d');
            $vtuber['birthday'] = $faker->dateTimeBetween('-30 years', '-18 years')->format('m-d');
            $vtuber['height'] = $faker->numberBetween(100, 300);
            $vtuber['avatar'] = 'vtubers/avatars/' . $slug . '.png';
            $vtuber['banner'] = 'vtubers/banners/' . $slug . '.png';
            $vtuber['created_at'] = Carbon::now();
            $vtuber['updated_at'] = Carbon::now();

            Vtuber::create($vtuber);
        }
    }
}
