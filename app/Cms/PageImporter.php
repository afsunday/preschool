<?php

namespace App\Cms;

use App\Models\Page;
use App\Models\PageBlock;
use Illuminate\Support\Str;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Validator;
use RuntimeException;

/**
 * Seeds pages into the DB from version-controlled JSON blueprints under
 * resources/cms/pages. Two-layer validation: the blueprint's *shape* against the
 * global JSON Schema, then each block's *settings* through its BlockType.
 *
 * A blueprint is a seed, not a live config file — once a page exists, the editor
 * owns its content. Hence four distinct operations:
 *
 *   syncNew()         import pages that don't exist yet.
 *   syncBlocks()      add blueprint blocks the page hasn't got, matched by `key`.
 *   reconcileBlocks() bring existing blocks' settings up to the current schema.
 *   import()          rebuild a page from scratch — destructive, discards edits.
 */
class PageImporter
{
    public function __construct(protected BlockTypeRegistry $registry) {}

    protected function dir(): string
    {
        return resource_path('cms/pages');
    }

    protected function schemaPath(): string
    {
        return resource_path('cms/page.schema.json');
    }

    /** The site chrome. Not under pages/ — it has blocks but no route. */
    protected function globalsPath(): string
    {
        return resource_path('cms/globals.json');
    }

    /**
     * Import every blueprint whose slug isn't already in the DB.
     *
     * @return array<int, string> imported slugs
     */
    public function syncNew(): array
    {
        $imported = [];
        $files = glob($this->dir().'/*.json') ?: [];

        // The globals blueprint is a page like any other — it just has no route.
        if (is_file($this->globalsPath())) {
            array_unshift($files, $this->globalsPath());
        }

        foreach ($files as $file) {
            $blueprint = $this->read($file);

            if (Page::where('slug', $blueprint['slug'])->exists()) {
                continue; // already in the DB — the editor owns it now
            }

            $this->import($blueprint);
            $imported[] = $blueprint['slug'];
        }

        return $imported;
    }

    /**
     * Add blocks the blueprint has and the page hasn't, matched on `key`.
     *
     * Append-only and non-destructive:
     *   - key already on the page   -> left alone, edits and all
     *   - key the page hasn't got   -> appended at the end
     *   - block added in the editor -> has no key, so never touched
     *   - block dropped from the JSON -> stays on the page
     *
     * Appended rather than inserted at the blueprint's index: the moment anyone
     * edits the page the two orders diverge, so a blueprint position would be
     * asserting something it cannot know. Drag it where you want it.
     *
     * @return array<int, string> keys added
     */
    public function syncBlocks(string $slug): array
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        $blueprint = $this->read($this->dir()."/{$slug}.json");

        $existing = $page->allBlocks()->whereNotNull('key')->pluck('key')->all();
        $position = (int) $page->allBlocks()->max('position');
        $added = [];

        foreach ($blueprint['blocks'] ?? [] as $block) {
            $key = $block['key'] ?? null;

            // Without a key the blueprint cannot claim a block, so skip it
            // rather than guess by type and risk duplicating an edited one.
            if ($key === null || in_array($key, $existing, true)) {
                continue;
            }

            if ($this->writeBlock($page, $block, null, ++$position) !== null) {
                $added[] = $key;
            }
        }

        return $added;
    }

    /**
     * Bring every existing block's settings up to the current schema, without
     * touching content the builder owns.
     *
     * For each block on the page, its stored settings are reconciled against its
     * block type: new fields are seeded from the matching blueprint block (by
     * `key`), removed fields are pruned, repeater rows are reshaped — but an
     * edited value is never overwritten. See {@see BlockType::reconcile()}.
     *
     * Runs on pull, after syncBlocks: append-then-reshape. Idempotent — a page
     * already on the current schema changes nothing.
     *
     * @return array<int, string> identifiers of blocks whose settings changed
     */
    public function reconcileBlocks(string $slug): array
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        $blueprint = $this->read($this->dir()."/{$slug}.json");

        // Blueprint settings indexed by key — the source for brand-new fields.
        $seedByKey = [];
        foreach ($blueprint['blocks'] ?? [] as $block) {
            if (($block['key'] ?? null) !== null) {
                $seedByKey[$block['key']] = $block['settings'] ?? [];
            }
        }

        $changed = [];

        foreach ($page->allBlocks()->get() as $block) {
            $type = $this->registry->find($block->type);

            if ($type === null) {
                continue;
            }

            $before = $block->settings ?? [];
            $seed = $block->key !== null ? ($seedByKey[$block->key] ?? []) : [];
            $after = $type->reconcile($before, $seed);

            // Compared by meaning, not by literal shape: reconcile emits keys in
            // schema order, which need not match however the row was stored, and
            // a bare re-ordering of a map's keys is not a content change. Repeater
            // *rows* stay order-sensitive — their order is content.
            if (! $this->settingsEqual($after, $before) || $block->schema_version !== $type->version) {
                $block->update([
                    'settings' => $after,
                    'schema_version' => $type->version,
                ]);
                $changed[] = $block->key ?? "{$block->type}#{$block->id}";
            }
        }

        return $changed;
    }

    /**
     * Equality of two settings trees ignoring the key order of maps (which is
     * meaningless) but honouring the order of lists (repeater rows, which is
     * content).
     *
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $b
     */
    protected function settingsEqual(array $a, array $b): bool
    {
        return $this->canonicalise($a) === $this->canonicalise($b);
    }

    /**
     * ksort every map, top to bottom; leave lists in place.
     */
    protected function canonicalise(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $canon = array_map($this->canonicalise(...), $value);

        if (! array_is_list($canon)) {
            ksort($canon);
        }

        return $canon;
    }

    /**
     * Import one blueprint by slug (creates or replaces).
     */
    public function importSlug(string $slug): Page
    {
        return $this->import($this->read($this->dir()."/{$slug}.json"));
    }

    /**
     * @param  array<string, mixed>  $blueprint
     */
    public function import(array $blueprint): Page
    {
        $page = Page::updateOrCreate(
            ['slug' => $blueprint['slug']],
            [
                'title' => $blueprint['title'],
                'status' => $blueprint['status'] ?? 'draft',
                // Blueprint-backed: a route + Blade view exist, so it can't be deleted.
                'is_system' => true,
                'published_at' => ($blueprint['status'] ?? null) === 'published' ? now() : null,
                'meta_title' => data_get($blueprint, 'meta.title'),
                'meta_description' => data_get($blueprint, 'meta.description'),
            ],
        );

        $page->allBlocks()->delete();

        foreach ($blueprint['blocks'] ?? [] as $i => $block) {
            $this->writeBlock($page, $block, null, $i);
        }

        return $page;
    }

    /**
     * @param  array<string, mixed>  $block
     */
    protected function writeBlock(Page $page, array $block, ?int $parentId, int $position): ?PageBlock
    {
        $type = $this->registry->find($block['type'] ?? '');

        if ($type === null) {
            return null;
        }

        $row = $page->allBlocks()->create([
            'parent_id' => $parentId,
            'type' => $block['type'],
            'key' => $block['key'] ?? null,
            'name' => $block['name'] ?? null,
            'position' => $position,
            'is_visible' => $block['isVisible'] ?? true,
            'settings' => $type->validate((array) ($block['settings'] ?? [])),
            'schema_version' => $type->version,
        ]);

        foreach ($block['children'] ?? [] as $i => $child) {
            $this->writeBlock($page, $child, $row->id, $i);
        }

        return $row;
    }

    /**
     * Read + structurally validate a blueprint file against the global schema.
     *
     * @return array<string, mixed>
     */
    protected function read(string $file): array
    {
        $data = json_decode((string) file_get_contents($file));

        if ($data === null) {
            throw new RuntimeException('Invalid JSON in '.basename($file));
        }

        $validator = new Validator;
        $result = $validator->validate(
            $data,
            (string) file_get_contents($this->schemaPath()),
        );

        if (! $result->isValid()) {
            $errors = (new ErrorFormatter)->format($result->error());
            throw new RuntimeException(
                basename($file).' does not match the page schema: '
                .json_encode($errors),
            );
        }

        /** @var array<string, mixed> $assoc */
        $assoc = json_decode(json_encode($data), true);

        return $assoc;
    }

    /**
     * Blueprint slugs present on disk.
     *
     * @return array<int, string>
     */
    public function available(): array
    {
        return collect(glob($this->dir().'/*.json') ?: [])
            ->map(fn ($f) => Str::of(basename($f))->beforeLast('.json')->toString())
            ->values()
            ->all();
    }
}
