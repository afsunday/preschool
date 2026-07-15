<?php

namespace App\Cms;

/**
 * A single rendered section on a page — its handle, type, HTML and raw settings.
 */
class RenderedSection
{
    /**
     * @param  array<string, mixed>  $settings
     */
    public function __construct(
        public readonly ?string $name,
        public readonly string $type,
        public readonly string $html,
        public readonly array $settings,
    ) {}
}
