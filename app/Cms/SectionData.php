<?php

namespace App\Cms;

use App\Models\Media;

/**
 * Read-only view over a section's stored settings, handed to the Blade view.
 * Resolves media ids to URLs so templates never touch the DB directly.
 */
class SectionData
{
    /**
     * @param  array<string, mixed>  $settings
     */
    public function __construct(public readonly array $settings) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    public function mediaUrl(string $key): ?string
    {
        $id = $this->get($key);

        return $id ? Media::find($id)?->url() : null;
    }

    public function mediaAlt(string $key): ?string
    {
        $id = $this->get($key);

        return $id ? Media::find($id)?->alt : null;
    }
}
