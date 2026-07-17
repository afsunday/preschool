<?php

use App\Cms\BlockTypeRegistry;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['user_type' => 'admin']);
});

test('saving enforces the schema: unknown keys dropped, values coerced', function () {
    $page = Page::factory()->create();

    $this->actingAs($this->admin)->putJson(route('builder.save', $page), [
        'title' => 'T',
        'status' => 'draft',
        'blocks' => [[
            'type' => 'hero',
            'name' => 'main',
            'settings' => [
                'title' => 'Hello',
                'align' => 'bananas',       // not an allowed option
                'evil_key' => 'DROP TABLE',  // not in the schema
            ],
        ]],
    ])->assertOk();

    $section = PageBlock::where('page_id', $page->id)->firstOrFail();

    expect($section->settings)->toHaveKey('title', 'Hello');
    expect($section->settings)->not->toHaveKey('evil_key');   // unknown dropped
    expect($section->settings['align'])->toBe('center');       // select fell back to default
    expect($section->name)->toBe('main');                      // handle kept
    expect($section->schema_version)
        ->toBe(app(BlockTypeRegistry::class)->find('hero')->version); // version stamped
});

test('cms:sections:check reports blocks behind their schema version', function () {
    $page = Page::factory()->create();
    $page->allBlocks()->create([
        'type' => 'hero',
        'position' => 0,
        'is_visible' => true,
        'settings' => ['title' => 'Old'],
        'schema_version' => 0, // behind current (1)
    ]);

    $this->artisan('cms:sections:check')
        ->expectsOutputToContain('behind their current schema')
        ->assertExitCode(0);
});
