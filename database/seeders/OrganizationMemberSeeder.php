<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class OrganizationMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $organizations = DB::table('organizations')->pluck('id')->all();
        $vtubers = DB::table('vtubers')->where('current_affiliation', 'organization')->get();

        if (empty($organizations) || empty($vtubers)) {
            $this->command->warn('Skip OrganizationMemberSeeder: organizations/vtubers kosong atau tidak ada vtuber dengan current_affiliation "organization".');

            return;
        }

        $generations = ['Gen 1', 'Gen 2', 'Gen 3'];
        $statuses = array_merge(
            array_fill(0, 4, 'active'),
            // array_fill(0, 1, 'graduated'),
            // array_fill(0, 1, 'left'),
        );

        foreach ($vtubers as $vtuber) {
            $status = $faker->randomElement($statuses);
            $joinedAt = $faker->dateTimeBetween('-4 year', '-1 month');

            $leftAt = null;
            if ($status !== 'active') {
                $leftAt = $faker->dateTimeBetween($joinedAt, 'now')->format('Y-m-d');
            }

            DB::table('organization_members')->insert([
                'organization_id' => $faker->randomElement($organizations),
                'vtuber_id' => $vtuber->id,
                'generation' => $faker->randomElement($generations),
                'joined_at' => $joinedAt->format('Y-m-d'),
                'left_at' => $leftAt,
                'status' => $status,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            if ($status !== 'active') {
                DB::table('vtubers')
                    ->where('id', $vtuber->id)
                    ->update([
                        'current_affiliation' => 'independent',
                    ]);
            }
        }
    }
}
