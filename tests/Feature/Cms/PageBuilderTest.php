<?php

use App\Models\Media;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['user_type' => 'admin']);
});

test('the schema endpoint returns the block types that page can render', function () {
    // Schemas are page-scoped now: a home-slugged page is offered home's own
    // block types plus the globals'.
    $page = Page::factory()->create(['slug' => 'home']);

    $response = $this->actingAs($this->admin)
        ->getJson(route('builder.schema', $page))
        ->assertOk();

    $keys = collect($response->json('data'))->pluck('key');

    expect($keys)->toContain('hero')->toContain('home_hero')->toContain('steps');
});

test('non-admins are blocked from the builder api', function () {
    $page = Page::factory()->create(['slug' => 'home']);

    $this->actingAs(User::factory()->create(['user_type' => 'user']))
        ->getJson(route('builder.schema', $page))
        ->assertForbidden();
});

test('saving a page persists sections, mirrors media, and snapshots', function () {
    $page = Page::factory()->create(['title' => 'Home', 'status' => 'draft']);
    $image = Media::factory()->create(['kind' => 'image']);

    $doc = [
        'title' => 'Home Page',
        'status' => 'published',
        'meta' => ['title' => 'Welcome', 'description' => 'Hi', 'ogImage' => 'media/og-share.png'],
        'blocks' => [
            [
                'type' => 'hero',
                'isVisible' => true,
                'settings' => [
                    'title' => 'Where little minds come alive',
                    'align' => 'center',
                    'image' => $image->id,
                ],
                'children' => [],
            ],
        ],
    ];

    $this->actingAs($this->admin)
        ->putJson(route('builder.save', $page), $doc)
        ->assertOk()
        ->assertJsonPath('data.title', 'Home Page')
        ->assertJsonPath('data.status', 'published')
        ->assertJsonPath('data.blocks.0.type', 'hero')
        ->assertJsonPath('data.blocks.0.settings.title', 'Where little minds come alive');

    $page->refresh();
    expect($page->status)->toBe('published');
    expect($page->published_at)->not->toBeNull();
    expect($page->og_image)->toBe('media/og-share.png'); // stored as a path, not a media id

    $section = PageBlock::where('page_id', $page->id)->firstOrFail();

    // Media mirrored into the pivot (so "where is this used?" works).
    $this->assertDatabaseHas('mediables', [
        'media_id' => $image->id,
        'mediable_type' => PageBlock::class,
        'mediable_id' => $section->id,
        'collection' => 'settings',
    ]);

    // A revision was snapshotted.
    expect($page->revisions()->count())->toBe(1);
});

test('re-saving replaces the section tree', function () {
    $page = Page::factory()->create();

    $save = fn (array $blocks) => $this->actingAs($this->admin)->putJson(
        route('builder.save', $page),
        ['title' => 'T', 'status' => 'draft', 'blocks' => $blocks],
    );

    $save([['type' => 'hero', 'settings' => ['title' => 'A']]])->assertOk();
    $save([['type' => 'hero', 'settings' => ['title' => 'B']]])->assertOk();

    expect(PageBlock::where('page_id', $page->id)->count())->toBe(1);
    expect(PageBlock::where('page_id', $page->id)->first()->settings['title'])->toBe('B');
});

test('renderPage returns the full page html from the current doc', function () {
    $page = Page::factory()->create(['slug' => 'home']);

    $this->actingAs($this->admin)
        ->postJson(route('builder.render', $page), [
            'title' => 'Home',
            'blocks' => [
                ['type' => 'home_hero', 'settings' => ['title' => 'Hello there']],
            ],
        ])
        ->assertOk()
        ->assertSee('Hello there', escape: false);
});
