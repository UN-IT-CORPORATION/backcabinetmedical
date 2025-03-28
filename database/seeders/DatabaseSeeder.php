<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test users with factory if needed
        // User::factory(10)->create();

        // Create a specific admin/test user
        User::create([
            'email' => 'example@gmail.com',
            'password' => Hash::make('1234'),
        ]);
    }
}
