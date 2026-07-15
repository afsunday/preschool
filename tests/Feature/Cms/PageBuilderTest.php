<?php

use App\Models\Media;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['user_type' => 'admin']);
});

test('the schema endpoint returns every section schema', function () {
    $response = $this->actingAs($this->admin)
        ->getJson(route('builder.schema'))
        ->assertOk();

    $keys = collect($response->json('data'))->pluck('key');

    expect($keys)->toContain('hero')->toContain('home_hero')->toContain('steps');
});

test('non-admins are blocked from the builder api', function () {
    $this->actingAs(User::factory()->create(['user_type' => 'user']))
        ->getJson(route('builder.schema'))
        ->assertForbidden();
});

test('saving a page persists sections, mirrors media, and snapshots', function () {
    $page = Page::factory()->create(['title' => 'Home', 'status' => 'draft']);
    $image = Media::factory()->create(['kind' => 'image']);

    $doc = [
        'title' => 'Home Page',
        'status' => 'published',
        'meta' => ['title' => 'Welcome', 'description' => 'Hi', 'ogMediaId' => $image->id],
        'sections' => [
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
        ->assertJsonPath('data.sections.0.type', 'hero')
        ->assertJsonPath('data.sections.0.settings.title', 'Where little minds come alive');

    $page->refresh();
    expect($page->status)->toBe('published');
    expect($page->published_at)->not->toBeNull();

    $section = PageSection::where('page_id', $page->id)->firstOrFail();

    // Media mirrored into the pivot (so "where is this used?" works).
    $this->assertDatabaseHas('mediables', [
        'media_id' => $image->id,
        'mediable_type' => PageSection::class,
        'mediable_id' => $section->id,
        'collection' => 'settings',
    ]);

    // A revision was snapshotted.
    expect($page->revisions()->count())->toBe(1);
});

test('re-saving replaces the section tree', function () {
    $page = Page::factory()->create();

    $save = fn (array $sections) => $this->actingAs($this->admin)->putJson(
        route('builder.save', $page),
        ['title' => 'T', 'status' => 'draft', 'sections' => $sections],
    );

    $save([['type' => 'hero', 'settings' => ['title' => 'A']]])->assertOk();
    $save([['type' => 'hero', 'settings' => ['title' => 'B']]])->assertOk();

    expect(PageSection::where('page_id', $page->id)->count())->toBe(1);
    expect(PageSection::where('page_id', $page->id)->first()->settings['title'])->toBe('B');
});

test('render-section returns html for the preview', function () {
    $this->actingAs($this->admin)
        ->postJson(route('builder.render'), [
            'type' => 'hero',
            'settings' => ['title' => 'Hello there', 'align' => 'center'],
        ])
        ->assertOk()
        ->assertJsonFragment([])
        ->assertSee('Hello there', escape: false);
});
