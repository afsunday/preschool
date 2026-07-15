<?php

namespace App\Cms;

/**
 * Discovers block types from the page JSON files. Each page file may declare a
 * `blocks` map (the page's own block definitions: schema + view). The registry
 * aggregates them into one lookup — so a block defined on any page is available
 * to the editor and renderer. Pure JSON, no eval, no Blade parsing.
 */
class SectionRegistry
{
    /** @var array<string, SectionDefinition>|null */
    protected ?array $sections = null;

    /**
     * @return array<int, string>
     */
    protected function files(): array
    {
        return glob(resource_path('cms/pages').'/*.json') ?: [];
    }

    /**
     * @return array<string, SectionDefinition>
     */
    public function all(): array
    {
        if ($this->sections !== null) {
            return $this->sections;
        }

        $this->sections = [];

        foreach ($this->files() as $file) {
            $doc = json_decode((string) file_get_contents($file), true);
            $blocks = $doc['blocks'] ?? null;

            if (! is_array($blocks)) {
                continue;
            }

            foreach ($blocks as $key => $def) {
                if (isset($this->sections[$key]) || ! is_array($def)) {
                    continue; // first definition wins
                }

                $this->sections[$key] = new SectionDefinition(
                    key: $key,
                    name: $def['name'] ?? $key,
                    group: $def['group'] ?? 'Content',
                    version: (int) ($def['version'] ?? 1),
                    acceptsChildren: (bool) ($def['acceptsChildren'] ?? false),
                    view: $def['view'] ?? 'blocks.'.str_replace('_', '-', $key),
                    fieldSpecs: $def['fields'] ?? [],
                );
            }
        }

        return $this->sections;
    }

    public function find(string $key): ?SectionDefinition
    {
        return $this->all()[$key] ?? null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function schemas(): array
    {
        return array_values(array_map(
            fn (SectionDefinition $s) => $s->schema(),
            $this->all(),
        ));
    }
}
