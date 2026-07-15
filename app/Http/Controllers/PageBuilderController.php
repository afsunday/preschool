<?php

namespace App\Http\Controllers;

use App\Cms\BladeRenderer;
use App\Cms\Fields\Media as MediaField;
use App\Cms\SectionRegistry;
use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PageBuilderController extends Controller
{
    public function __construct(
        protected SectionRegistry $registry,
        protected BladeRenderer $renderer,
    ) {}

    /**
     * The pages list (Inertia).
     */
    public function index(): Response
    {
        $pages = Page::query()
            ->withCount('allSections as sections_count')
            ->latest('updated_at')
            ->get()
            ->map(fn (Page $p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'status' => $p->status,
                'sectionsCount' => $p->sections_count,
                'updatedAt' => $p->updated_at?->diffForHumans(),
            ]);

        return Inertia::render('cms/pages-index', ['pages' => $pages]);
    }

    /**
     * Create a page and jump straight into the editor.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $page = Page::create([
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['title']),
            'status' => 'draft',
        ]);

        return to_route('pages.edit', $page);
    }

    public function destroy(Page $page): \Illuminate\Http\RedirectResponse
    {
        $page->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Page deleted.')]);

        return to_route('pages.index');
    }

    /**
     * Pull in new pages from the resources/cms/pages blueprints (only ones not
     * already in the DB — the editor owns existing pages).
     */
    public function pull(\App\Cms\PageImporter $importer): \Illuminate\Http\RedirectResponse
    {
        $imported = $importer->syncNew();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $imported === []
                ? __('No new pages to import.')
                : __('Imported :count page(s): :slugs.', [
                    'count' => count($imported),
                    'slugs' => implode(', ', $imported),
                ]),
        ]);

        return to_route('pages.index');
    }

    /**
     * The editor screen (Inertia) for a page.
     */
    public function edit(Page $page): Response
    {
        return Inertia::render('cms/page-editor', ['pageId' => $page->id]);
    }

    protected function uniqueSlug(string $title): string
    {
        $base = \Illuminate\Support\Str::slug($title) ?: 'page';
        $slug = $base;
        $i = 2;

        while (Page::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    /**
     * All section schemas — draws the editor's "add section" list + field panel.
     */
    public function schema(): JsonResponse
    {
        return response()->json(['data' => $this->registry->schemas()]);
    }

    /**
     * The page document the editor loads.
     */
    public function show(Page $page): JsonResponse
    {
        return response()->json(['data' => $this->pageDoc($page)]);
    }

    /**
     * Persist the page document: replace sections, mirror media, snapshot.
     */
    public function save(Request $request, Page $page): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:draft,published'],
            'meta.title' => ['nullable', 'string', 'max:255'],
            'meta.description' => ['nullable', 'string'],
            'meta.ogMediaId' => ['nullable', 'integer', 'exists:media,id'],
            'headerScripts' => ['nullable', 'string'],
            'footerScripts' => ['nullable', 'string'],
            'sections' => ['array'],
        ]);

        DB::transaction(function () use ($request, $page, $validated) {
            $page->update([
                'title' => $validated['title'],
                'status' => $validated['status'],
                'published_at' => $validated['status'] === 'published'
                    ? ($page->published_at ?? now())
                    : null,
                'meta_title' => data_get($validated, 'meta.title'),
                'meta_description' => data_get($validated, 'meta.description'),
                'og_media_id' => data_get($validated, 'meta.ogMediaId'),
                'header_scripts' => $validated['headerScripts'] ?? null,
                'footer_scripts' => $validated['footerScripts'] ?? null,
            ]);

            // Rebuild the section tree from scratch (editor reloads fresh ids).
            $page->allSections()->delete();
            $this->writeSections($page, $request->input('sections', []), null);

            $page->refresh();

            $page->revisions()->create([
                'user_id' => $request->user()?->id,
                'snapshot' => $this->pageDoc($page),
            ]);
        });

        return response()->json(['data' => $this->pageDoc($page->refresh())]);
    }

    /**
     * The page rendered with site.css, loaded into the editor's iframe. In
     * editor mode each section is wrapped for selection and a postMessage bridge
     * lets the editor swap/insert/remove/reorder nodes without a reload.
     */
    public function preview(Request $request, Page $page): \Illuminate\Contracts\View\View
    {
        $sections = $page->allSections()->get()
            ->whereNull('parent_id')
            ->sortBy('position')
            ->map(fn (PageSection $s) => [
                'id' => $s->id,
                'html' => $this->renderer->render($s->type, $s->settings ?? []),
            ])
            ->values();

        return view('cms.preview', [
            'page' => $page,
            'sections' => $sections,
            'editor' => $request->boolean('editor'),
        ]);
    }

    /**
     * Render one section to HTML for the live preview.
     */
    public function renderSection(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'string'],
            'settings' => ['array'],
        ]);

        return response()->json([
            'html' => $this->renderer->render($data['type'], $data['settings'] ?? []),
        ]);
    }

    /**
     * Options for a relation field (binding sections). Stub until domain tables
     * exist; each `source` will map to a real model query.
     */
    public function options(string $source): JsonResponse
    {
        return response()->json(['data' => []]);
    }

    // ------------------------------------------------------------------ //

    /**
     * Recursively insert sections, mirroring media ids into `mediables`.
     *
     * @param  array<int, array<string, mixed>>  $sections
     */
    protected function writeSections(Page $page, array $sections, ?int $parentId): void
    {
        foreach ($sections as $i => $incoming) {
            $type = $incoming['type'] ?? null;
            $definition = is_string($type) ? $this->registry->find($type) : null;
            if ($definition === null) {
                continue; // unknown block type — skip
            }

            // Enforce the schema: drop unknown keys, coerce values by field type.
            $settings = $definition->validate((array) ($incoming['settings'] ?? []));
            $name = $incoming['name'] ?? null;

            $section = $page->allSections()->create([
                'parent_id' => $parentId,
                'type' => $type,
                'name' => is_string($name) && $name !== '' ? $name : null,
                'position' => $i,
                'is_visible' => (bool) ($incoming['isVisible'] ?? true),
                'settings' => $settings,
                'schema_version' => $definition->version,
            ]);

            $this->mirrorMedia($section, $type, $settings);

            if (! empty($incoming['children']) && is_array($incoming['children'])) {
                $this->writeSections($page, $incoming['children'], $section->id);
            }
        }
    }

    /**
     * Attach every media id referenced by a section's Media fields to the
     * `mediables` pivot, so media stays relational despite JSON settings.
     *
     * @param  array<string, mixed>  $settings
     */
    protected function mirrorMedia(PageSection $section, string $type, array $settings): void
    {
        $mediaIds = Collection::make($this->registry->find($type)?->fields() ?? [])
            ->filter(fn ($f) => $f instanceof MediaField)
            ->flatMap(fn (MediaField $f) => (array) data_get($settings, $f->id))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        foreach ($mediaIds as $position => $mediaId) {
            DB::table('mediables')->updateOrInsert(
                [
                    'media_id' => $mediaId,
                    'mediable_type' => PageSection::class,
                    'mediable_id' => $section->id,
                    'collection' => 'settings',
                ],
                ['position' => $position, 'updated_at' => now(), 'created_at' => now()],
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function pageDoc(Page $page): array
    {
        $all = $page->allSections()->get();

        return [
            'id' => $page->id,
            'slug' => $page->slug,
            'title' => $page->title,
            'status' => $page->status,
            'meta' => [
                'title' => $page->meta_title,
                'description' => $page->meta_description,
                'ogMediaId' => $page->og_media_id,
            ],
            'headerScripts' => $page->header_scripts,
            'footerScripts' => $page->footer_scripts,
            'sections' => $this->sectionTree($all, null),
        ];
    }

    /**
     * @param  Collection<int, PageSection>  $all
     * @return array<int, array<string, mixed>>
     */
    protected function sectionTree(Collection $all, ?int $parentId): array
    {
        return $all
            ->where('parent_id', $parentId)
            ->sortBy('position')
            ->map(fn (PageSection $s) => [
                'id' => $s->id,
                'type' => $s->type,
                'name' => $s->name,
                'position' => $s->position,
                'isVisible' => $s->is_visible,
                'schemaVersion' => $s->schema_version,
                'settings' => $s->settings ?? (object) [],
                'children' => $this->sectionTree($all, $s->id),
            ])
            ->values()
            ->all();
    }
}
