<?php

namespace App\Console\Commands;

use App\Cms\SectionRegistry;
use App\Models\PageSection;
use Illuminate\Console\Command;

class CheckSectionSchemas extends Command
{
    protected $signature = 'cms:sections:check';

    protected $description = 'Report page blocks whose stored schema version is behind the current section version';

    public function handle(SectionRegistry $registry): int
    {
        $stale = PageSection::query()->get()->filter(function (PageSection $s) use ($registry) {
            $definition = $registry->find($s->type);

            return $definition && $s->schema_version < $definition->version;
        });

        if ($stale->isEmpty()) {
            $this->info('All blocks are on the current schema version.');

            return self::SUCCESS;
        }

        $this->warn($stale->count().' block(s) behind their current schema:');

        foreach ($stale as $s) {
            $current = $registry->find($s->type)->version;
            $this->line("  page {$s->page_id}  #{$s->id}  {$s->type}  v{$s->schema_version} → v{$current}");
        }

        $this->newLine();
        $this->line('These are auto-migrated at render time; re-save the page to persist the upgrade.');

        return self::SUCCESS;
    }
}
