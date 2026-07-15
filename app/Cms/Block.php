<?php

namespace App\Cms;

use App\Models\Media;

/**
 * One block on a page, handed to the page view. Carries its identity (id, type,
 * name) plus read helpers over its stored settings. The page view loops these
 * and renders each block's markup inline (@switch on ->type).
 */
class Block
{
    /**
     * @param  array<string, mixed>  $settings
     */
    public function __construct(
        public readonly int $id,
        public readonly string $type,
        public readonly ?string $name,
        public readonly array $settings,
    ) {}

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
