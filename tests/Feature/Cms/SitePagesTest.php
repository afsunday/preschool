<?php

use App\Cms\BlockTypeRegistry;
use App\Cms\PageImporter;
use App\Http\Controllers\SitePageController;
use App\Models\Page;
use App\Models\User;

test('every public page imports from its blueprint and renders', function (string $slug, string $route) {
    $page = app(PageImporter::class)->importSlug($slug);

    expect($page->status)->toBe('published');
    expect($page->allBlocks()->count())->toBeGreaterThan(0);

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
        ->toBe($page->allBlocks()->whereNull('parent_id')->count());
})->with(['about', 'admissions', 'resources', 'gallery', 'forms', 'faq', 'contact']);

test('a page renders the copy held in its blocks, not hardcoded markup', function () {
    app(PageImporter::class)->importSlug('about');

    $page = Page::where('slug', 'about')->firstOrFail();
    $page->allBlocks()
        ->where('type', 'about_hero')
        ->firstOrFail()
        ->update(['settings' => ['title' => 'Edited in the editor']]);

    $this->get(route('about'))
        ->assertOk()
        ->assertSee('Edited in the editor')
        ->assertDontSee('More than daycare'); // the blueprint's copy is gone
});

test('globals are returned keyed by block type, not as an ordered list', function () {
    // The layout looks chrome up by type ($globals['site_navbar']); there is one
    // of each and its placement is the layout's call, so ordering is meaningless.
    app(PageImporter::class)->syncNew();

    $globals = SitePageController::globals();

    expect($globals->keys()->all())
        ->toEqualCanonicalizing(['site_navbar', 'newsletter', 'site_footer']);
    expect($globals['site_navbar']->type)->toBe('site_navbar');
    expect($globals['site_footer']->get('watermark'))->toBe('WODI DAYCARE');
});

test('globals resolve to an empty collection before the page is seeded', function () {
    // A fresh DB (no _globals row) must render bare chrome, not fatal.
    expect(SitePageController::globals())->toBeEmpty();
});

test('a public page renders the global navbar and footer from the blueprint', function () {
    app(PageImporter::class)->syncNew();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Enroll Your Child Today') // navbar CTA
        ->assertSee('WODI DAYCARE');           // footer watermark
});

test('a page outputs its social-share image as an absolute og:image', function () {
    app(PageImporter::class)->importSlug('about');
    Page::where('slug', 'about')->firstOrFail()
        ->update(['og_image' => 'storage/media/share.png', 'meta_description' => 'About WODI']);

    $this->get(route('about'))
        ->assertOk()
        ->assertSee('og:image', false)
        ->assertSee(url('storage/media/share.png'), false)
        ->assertSee('og:description', false);
});

test('the globals page is previewable in the editor', function () {
    app(PageImporter::class)->syncNew();
    $page = Page::where('slug', SitePageController::GLOBALS)->firstOrFail();

    $response = $this->actingAs(User::factory()->create(['user_type' => 'admin']))
        ->get(route('builder.preview', $page))
        ->assertOk()
        ->assertSee('Enroll Your Child Today') // navbar CTA
        ->assertSee('WODI DAYCARE');           // footer watermark

    // Header, newsletter and footer are each wrapped so the editor can select them.
    expect(substr_count($response->getContent(), '<div data-cms-block="'))->toBe(3);
});

test('the globals preview reflects unsaved edits to a block', function () {
    app(PageImporter::class)->syncNew();
    $page = Page::where('slug', SitePageController::GLOBALS)->firstOrFail();
    $footer = $page->allBlocks()->where('type', 'site_footer')->firstOrFail();

    $response = $this->actingAs(User::factory()->create(['user_type' => 'admin']))
        ->postJson(route('builder.render', $page), [
            'title' => $page->title,
            'status' => $page->status,
            'blocks' => [[
                'id' => $footer->id,
                'type' => 'site_footer',
                'settings' => array_merge($footer->settings ?? [], ['watermark' => 'EDITED MARK']),
            ]],
        ])
        ->assertOk();

    expect($response->json('html'))->toContain('EDITED MARK');
});

test('block type keys are unique across the blueprints', function () {
    // all() is first-wins, so a collision would silently shadow a type.
    $seen = [];

    $files = glob(resource_path('cms/pages').'/*.json');
    $files[] = resource_path('cms/globals.json');

    foreach ($files as $file) {
        $types = json_decode((string) file_get_contents($file), true)['blockTypes'] ?? [];

        foreach (array_keys($types) as $key) {
            expect($seen)->not->toHaveKey($key, "duplicate block type [{$key}]");
            $seen[$key] = basename($file);
        }
    }

    expect(app(BlockTypeRegistry::class)->all())->toHaveCount(count($seen));
});

test('every block a blueprint places is defined in the registry', function () {
    $registry = app(BlockTypeRegistry::class);

    foreach (glob(resource_path('cms/pages').'/*.json') as $file) {
        $doc = json_decode((string) file_get_contents($file), true);

        foreach ($doc['blocks'] ?? [] as $block) {
            expect($registry->find($block['type']))
                ->not->toBeNull("{$doc['slug']} places undefined block [{$block['type']}]");
        }
    }
});
