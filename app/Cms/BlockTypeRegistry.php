<?php

namespace App\Cms;

/**
 * The block types available to the CMS, read from the `blockTypes` map in each
 * blueprint. Pure JSON — no eval, no Blade parsing.
 *
 * Scoped, not global. A page may use its own types plus the globals' — because
 * the markup for a block lives in that page's own view (`@case` in the @switch),
 * so offering every type on every page would offer blocks the page renders as
 * nothing.
 */
class BlockTypeRegistry
{
    /** @var array<string, array<string, BlockType>>|null  source slug => types */
    protected ?array $bySource = null;

    /** Types every page may use. */
    public const GLOBALS = '_globals';

    protected function load(): array
    {
        if ($this->bySource !== null) {
            return $this->bySource;
        }

        $this->bySource = [];

        $files = glob(resource_path('cms/pages').'/*.json') ?: [];
        $globals = resource_path('cms/globals.json');

        if (is_file($globals)) {
            $files[] = $globals;
        }

        foreach ($files as $file) {
            $doc = json_decode((string) file_get_contents($file), true);

            if (! is_array($doc)) {
                continue;
            }

            $slug = $doc['slug'] ?? basename($file, '.json');
            $types = $doc['blockTypes'] ?? [];

            foreach ($types as $key => $def) {
                if (! is_array($def)) {
                    continue;
                }

                $this->bySource[$slug][$key] = new BlockType(
                    key: $key,
                    name: $def['name'] ?? $key,
                    group: $def['group'] ?? 'Content',
                    version: (int) ($def['version'] ?? 1),
                    acceptsChildren: (bool) ($def['acceptsChildren'] ?? false),
                    fieldSpecs: $def['fields'] ?? [],
                );
            }
        }

        return $this->bySource;
    }

    /**
     * Every type, from every source. Used to render and validate existing
     * blocks — never to decide what a page may add.
     *
     * @return array<string, BlockType>
     */
    public function all(): array
    {
        $flat = [];

        foreach ($this->load() as $types) {
            foreach ($types as $key => $type) {
                $flat[$key] ??= $type; // first definition wins
            }
        }

        return $flat;
    }

    /**
     * The types a page may actually use: its own, plus the globals'.
     *
     * @return array<string, BlockType>
     */
    public function forPage(string $slug): array
    {
        return [
            ...($this->load()[self::GLOBALS] ?? []),
            ...($this->load()[$slug] ?? []),
        ];
    }

    public function find(string $key): ?BlockType
    {
        return $this->all()[$key] ?? null;
    }

    /**
     * Serialised schemas for the editor. Pass a slug to scope them to one page.
     *
     * @return array<int, array<string, mixed>>
     */
    public function schemas(?string $slug = null): array
    {
        $types = $slug === null ? $this->all() : $this->forPage($slug);

        return array_values(array_map(fn (BlockType $t) => $t->schema(), $types));
    }
}
