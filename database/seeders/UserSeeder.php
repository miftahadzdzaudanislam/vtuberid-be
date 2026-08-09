<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin vtuberid',
            'email' => 'miftah@vtuberid.com',
            'role' => 'admin',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Editor vtuberid',
            'email' => 'adz@vtuberid.com',
            'role' => 'editor',
            'password' => Hash::make('password123'),
        ]);
    }
}
