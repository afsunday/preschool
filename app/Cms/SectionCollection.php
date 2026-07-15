<?php

namespace App\Cms;

use Illuminate\Support\Collection;

/**
 * The rendered blocks of a page — a full Laravel collection, so it's both
 * iterable (`@foreach`) and queryable (`filter`, `map`, `where`, `first`, …),
 * with block-aware helpers on top:
 *
 *   $sections->section('hero')?->html      // one, by handle then type
 *   $sections->ofType('testimonials')      // all of a type
 *   foreach ($sections as $section) { ... }
 *
 * @extends Collection<int, RenderedSection>
 */
class SectionCollection extends Collection
{
    /**
     * Find a block by its instance name, falling back to its type.
     */
    public function section(string $handle): ?RenderedSection
    {
        return $this->first(
            fn (RenderedSection $s) => $s->name === $handle || $s->type === $handle,
        );
    }

    /**
     * Is there a block with this handle/type?
     */
    public function has($key): bool
    {
        return is_string($key) && $this->section($key) !== null;
    }

    /**
     * All blocks of a given type.
     *
     * @return static
     */
    public function ofType(string $type): static
    {
        return $this
            ->filter(fn (RenderedSection $s) => $s->type === $type)
            ->values();
    }
}
