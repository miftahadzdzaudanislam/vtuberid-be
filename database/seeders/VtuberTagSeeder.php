<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class VtuberTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $vtubers = DB::table('vtubers')->get();
        $tags = DB::table('tags')->get();

        if ($vtubers->isEmpty() || $tags->isEmpty()) {
            $this->command->warn('Skip VtuberTagSeeder: vtubers/tags masih kosong.');
            
            return;
        }
        
        foreach ($vtubers as $vtuber) {
            $tagCount = min($tags->count(), $faker->numberBetween(1, 5));
            $selectedTags = $tags->random($tagCount);

            foreach ($selectedTags as $tag) {
                DB::table('vtuber_tags')->insert([
                    'vtuber_id'  => $vtuber->id,
                    'tag_id'     => $tag->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}
