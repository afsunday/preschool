<?php

namespace App\Cms;

use Illuminate\Support\Facades\Blade;

/**
 * Renders a block to HTML from its inline template (declared in a page view's
 * @block). One implementation of rendering; a headless front end would serve the
 * same block data as JSON instead.
 */
class BladeRenderer
{
    public function __construct(protected SectionRegistry $registry) {}

    /**
     * @param  array<string, mixed>  $settings
     */
    public function render(string $type, array $settings): string
    {
        $section = $this->registry->find($type);

        if ($section === null) {
            return '';
        }

        // Blade::render caches compiled output by content hash, so repeated
        // renders of the same block template are cheap. `$block` is the block's
        // data (a SectionData over its stored settings).
        return Blade::render($section->template, [
            'block' => new SectionData($settings),
            'settings' => $settings,
        ]);
    }
}
