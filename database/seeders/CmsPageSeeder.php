<?php

namespace Database\Seeders;

use App\Cms\PageImporter;
use Illuminate\Database\Seeder;

/**
 * Rebuild the public site from the blueprints in resources/cms/pages/.
 *
 * Without this a `migrate:fresh --seed` leaves zero pages and every public URL
 * 404s — the blueprints are the source of truth, so seeding must import them.
 *
 * Only imports slugs that aren't already in the DB (PageImporter::syncNew), so
 * running it against a live database never clobbers edited content.
 */
class CmsPageSeeder extends Seeder
{
    public function __construct(protected PageImporter $importer) {}

    public function run(): void
    {
        $imported = $this->importer->syncNew();

        $this->command?->info('  Imported '.count($imported).' CMS page(s): '.implode(', ', $imported));
    }
}
