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

        $members = [
            [
                'organization_id' => 4,
                'vtuber_id' => 4,
                'generation' => '',
                'left_at' => '2026-06-01',
                'status' => 'left',
            ],
            [
                'organization_id' => 2,
                'vtuber_id' => 5,
                'generation' => 'Gen 3',
                'left_at' => null,
                'status' => 'active',
            ],
            [
                'organization_id' => 3,
                'vtuber_id' => 5,
                'generation' => '',
                'left_at' => null,
                'status' => 'active',
            ],
            [
                'organization_id' => 2,
                'vtuber_id' => 6,
                'generation' => 'Gen 3',
                'left_at' => null,
                'status' => 'active',
            ],
            [
                'organization_id' => 2,
                'vtuber_id' => 7,
                'generation' => 'Gen 4',
                'left_at' => '2026-03-01',
                'status' => 'graduated',
            ],
            [
                'organization_id' => 1,
                'vtuber_id' => 8,
                'generation' => 'Chapter:01',
                'left_at' => null,
                'status' => 'active',
            ],
            [
                'organization_id' => 1,
                'vtuber_id' => 9,
                'generation' => 'Chapter:01',
                'left_at' => null,
                'status' => 'active',
            ],
            [
                'organization_id' => 1,
                'vtuber_id' => 10,
                'generation' => 'Chapter:01',
                'left_at' => '2025-11-01',
                'status' => 'graduated',
            ],
        ];

        foreach ($members as $member) {
            DB::table('organization_members')->insert([
                'organization_id' => $member['organization_id'],
                'vtuber_id' => $member['vtuber_id'],
                'generation' => $member['generation'],
                'joined_at' => $faker->dateTimeBetween('-4 year', '-1 month'),
                'left_at' => $member['left_at'],
                'status' => $member['status'],

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            if ($member['status'] !== 'active') {
                DB::table('vtubers')
                    ->where('id', $member['vtuber_id'])
                    ->update([
                        'current_affiliation' => 'independent',
                    ]);
            }
        };
    }
}
