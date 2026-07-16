<?php

use App\Cms\PageImporter;
use App\Cms\SectionRegistry;
use App\Models\Page;
use App\Models\User;

test('every public page imports from its blueprint and renders', function (string $slug, string $route) {
    $page = app(PageImporter::class)->importSlug($slug);

    expect($page->status)->toBe('published');
    expect($page->allSections()->count())->toBeGreaterThan(0);

    $this->get(route($route))->assertOk();
})->with([
    'home' => ['home', 'home'],
    'about' => ['about', 'about'],
    'admissions' => ['admissions', 'admissions'],
    'resources' => ['resources', 'resources'],
    'gallery' => ['gallery', 'gallery'],
    'forms' => ['forms', 'forms'],
    'faq' => ['faq', 'faq'],
    'contact' => ['contact', 'contact'],
]);

test('the editor preview wraps every block of a ported page', function (string $slug) {
    app(PageImporter::class)->importSlug($slug);
    $page = Page::where('slug', $slug)->firstOrFail();

    $response = $this->actingAs(User::factory()->create(['user_type' => 'admin']))
        ->get(route('builder.preview', $page))
        ->assertOk();

    expect(substr_count($response->getContent(), '<div data-cms-block="'))
        ->toBe($page->allSections()->whereNull('parent_id')->count());
})->with(['about', 'admissions', 'resources', 'gallery', 'forms', 'faq', 'contact']);

test('a page renders the copy held in its blocks, not hardcoded markup', function () {
    app(PageImporter::class)->importSlug('about');

    $page = Page::where('slug', 'about')->firstOrFail();
    $page->allSections()
        ->where('type', 'about_hero')
        ->firstOrFail()
        ->update(['settings' => ['title' => 'Edited in the editor']]);

    $this->get(route('about'))
        ->assertOk()
        ->assertSee('Edited in the editor')
        ->assertDontSee('Build credibility'); // the blueprint's copy is gone
});

test('block keys are unique across the page blueprints', function () {
    // The registry is first-wins, so a collision would silently shadow a block.
    $seen = [];

    foreach (glob(resource_path('cms/pages').'/*.json') as $file) {
        $blocks = json_decode((string) file_get_contents($file), true)['blocks'] ?? [];

        foreach (array_keys($blocks) as $key) {
            expect($seen)->not->toHaveKey($key, "duplicate block key [{$key}]");
            $seen[$key] = basename($file);
        }
    }

    expect(app(SectionRegistry::class)->all())->toHaveCount(count($seen));
});

test('every block a blueprint places is defined in the registry', function () {
    $registry = app(SectionRegistry::class);

    foreach (glob(resource_path('cms/pages').'/*.json') as $file) {
        $doc = json_decode((string) file_get_contents($file), true);

        foreach ($doc['sections'] ?? [] as $section) {
            expect($registry->find($section['type']))
                ->not->toBeNull("{$doc['slug']} places undefined block [{$section['type']}]");
        }
    }
});
