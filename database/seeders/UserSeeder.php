<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Password for both seeded accounts is "password".
        User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'user_type' => 'admin',
            'email' => 'admin@example.com',
        ]);

        User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'user_type' => 'user',
            'email' => 'test@example.com',
        ]);
    }
}
