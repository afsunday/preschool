<?php

namespace App\Cms;

use App\Models\Page;
use Illuminate\Support\Str;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Validator;
use RuntimeException;

/**
 * Seeds pages into the DB from version-controlled JSON blueprints under
 * resources/cms/pages. Two-layer validation: the blueprint's *shape* against the
 * global JSON Schema, then each block's *settings* through its Section schema.
 *
 * Blueprints are defaults/scaffold — once a page exists in the DB, the editor
 * owns it. `syncNew()` therefore only imports pages that don't exist yet.
 */
class PageImporter
{
    public function __construct(protected SectionRegistry $registry) {}

    protected function dir(): string
    {
        return resource_path('cms/pages');
    }

    protected function schemaPath(): string
    {
        return resource_path('cms/page.schema.json');
    }

    /**
     * Import every blueprint whose slug isn't already in the DB.
     *
     * @return array<int, string> imported slugs
     */
    public function syncNew(): array
    {
        $imported = [];

        foreach (glob($this->dir().'/*.json') ?: [] as $file) {
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
                'published_at' => ($blueprint['status'] ?? null) === 'published' ? now() : null,
                'meta_title' => data_get($blueprint, 'meta.title'),
                'meta_description' => data_get($blueprint, 'meta.description'),
            ],
        );

        $page->allSections()->delete();
        $this->writeBlocks($page, $blueprint['sections'] ?? [], null);

        return $page;
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    protected function writeBlocks(Page $page, array $blocks, ?int $parentId): void
    {
        foreach ($blocks as $i => $block) {
            $definition = $this->registry->find($block['type'] ?? '');
            if ($definition === null) {
                continue;
            }

            $row = $page->allSections()->create([
                'parent_id' => $parentId,
                'type' => $block['type'],
                'name' => $block['name'] ?? null,
                'position' => $i,
                'is_visible' => $block['isVisible'] ?? true,
                'settings' => $definition->validate((array) ($block['settings'] ?? [])),
                'schema_version' => $definition->version,
            ]);

            if (! empty($block['children'])) {
                $this->writeBlocks($page, $block['children'], $row->id);
            }
        }
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
