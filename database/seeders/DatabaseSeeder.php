<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(UserSeeder::class);
        // The public site lives in resources/cms/pages/*.json — without this a
        // fresh database serves no pages at all.
        $this->call(CmsPageSeeder::class);
        $this->call(PortalSeeder::class);
    }
}
