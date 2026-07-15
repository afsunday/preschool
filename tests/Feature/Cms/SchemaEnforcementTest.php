<?php

use App\Cms\PageRenderer;
use App\Cms\SectionRegistry;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['user_type' => 'admin']);
});

test('saving enforces the schema: unknown keys dropped, values coerced', function () {
    $page = Page::factory()->create();

    $this->actingAs($this->admin)->putJson(route('builder.save', $page), [
        'title' => 'T',
        'status' => 'draft',
        'sections' => [[
            'type' => 'hero',
            'name' => 'main',
            'settings' => [
                'title' => 'Hello',
                'align' => 'bananas',       // not an allowed option
                'evil_key' => 'DROP TABLE',  // not in the schema
            ],
        ]],
    ])->assertOk();

    $section = PageSection::where('page_id', $page->id)->firstOrFail();

    expect($section->settings)->toHaveKey('title', 'Hello');
    expect($section->settings)->not->toHaveKey('evil_key');   // unknown dropped
    expect($section->settings['align'])->toBe('center');       // select fell back to default
    expect($section->name)->toBe('main');                      // handle kept
    expect($section->schema_version)
        ->toBe(app(SectionRegistry::class)->find('hero')->version); // version stamped
});

test('the rendered collection is addressable by handle and type', function () {
    $page = Page::factory()->create();
    $page->allSections()->create([
        'type' => 'hero',
        'name' => 'top',
        'position' => 0,
        'is_visible' => true,
        'settings' => ['title' => 'Addressable'],
        'schema_version' => 1,
    ]);

    $sections = app(PageRenderer::class)->render($page);

    expect($sections)->toHaveCount(1);
    expect($sections->section('top'))->not->toBeNull();     // by name
    expect($sections->section('hero'))->not->toBeNull();    // by type
    expect($sections->section('top')->html)->toContain('Addressable');
    expect($sections->section('nope'))->toBeNull();
});

test('an out-of-date block is migrated at render time', function () {
    $page = Page::factory()->create();
    // Stored under a future-behind version; migrate() runs since current is >= 1.
    $page->allSections()->create([
        'type' => 'hero',
        'position' => 0,
        'is_visible' => true,
        'settings' => ['title' => 'Old'],
        'schema_version' => 0,
    ]);

    // Renders without error and reports as drift.
    expect(app(PageRenderer::class)->render($page)->section('hero')->html)
        ->toContain('Old');

    $this->artisan('cms:sections:check')
        ->expectsOutputToContain('behind their current schema')
        ->assertExitCode(0);
});
