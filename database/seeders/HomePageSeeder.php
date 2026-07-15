<?php

namespace Database\Seeders;

use App\Cms\PageImporter;
use Illuminate\Database\Seeder;

class HomePageSeeder extends Seeder
{
    public function run(): void
    {
        // The homepage's content lives in resources/cms/pages/home.json.
        app(PageImporter::class)->importSlug('home');
    }
}
