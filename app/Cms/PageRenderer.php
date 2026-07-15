<?php

namespace App\Cms;

use App\Models\Page;
use App\Models\PageSection;

/**
 * Turns a page's stored blocks into a rendered, addressable collection. The one
 * render path used by both the public site and the editor preview. Applies
 * schema migrations on the fly so instances saved under an older version still
 * render correctly.
 */
class PageRenderer
{
    public function __construct(
        protected BladeRenderer $renderer,
        protected SectionRegistry $registry,
    ) {}

    public function render(Page $page, bool $onlyVisible = true): SectionCollection
    {
        $rows = $page->allSections()->get()->whereNull('parent_id');

        if ($onlyVisible) {
            $rows = $rows->where('is_visible', true);
        }

        $sections = $rows
            ->sortBy('position')
            ->map(function (PageSection $row): RenderedSection {
                $settings = $this->currentSettings($row);

                return new RenderedSection(
                    $row->name,
                    $row->type,
                    $this->renderer->render($row->type, $settings),
                    $settings,
                );
            })
            ->values()
            ->all();

        return new SectionCollection($sections);
    }

    /**
     * Bring a row's settings up to the section's current schema version.
     *
     * @return array<string, mixed>
     */
    protected function currentSettings(PageSection $row): array
    {
        $settings = $row->settings ?? [];
        $section = $this->registry->find($row->type);

        if ($section && $row->schema_version < $section->version) {
            $settings = $section->migrate($settings, $row->schema_version);
        }

        return $settings;
    }
}
