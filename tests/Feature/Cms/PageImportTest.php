<?php

use App\Cms\PageImporter;
use App\Models\Page;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['user_type' => 'admin']);
});

test('a blueprint imports and is validated against the schema + block rules', function () {
    $page = app(PageImporter::class)->importSlug('home');

    expect($page->slug)->toBe('home');
    // 8, not 9: the newsletter is chrome now — one global block the layout
    // renders, rather than a copy on every page.
    expect($page->allBlocks()->count())->toBe(8);

    // Content was enforced through the block type's schema on import.
    $hero = $page->allBlocks()->where('type', 'home_hero')->first();
    expect($hero->name)->toBe('hero');
    expect($hero->schema_version)->toBe(1);
    expect($hero->settings)->toHaveKey('title', 'Where little minds come alive');
});

test('pull imports only pages not already in the DB', function () {
    // First pull: DB empty -> home imported.
    $this->actingAs($this->admin)
        ->post(route('pages.pull'))
        ->assertRedirect(route('pages.index'));

    expect(Page::where('slug', 'home')->exists())->toBeTrue();
    $count = Page::count();

    // Second pull: home already exists -> nothing new, no duplicates.
    $this->actingAs($this->admin)->post(route('pages.pull'));
    expect(Page::count())->toBe($count);
});

test('editor edits are not clobbered by a re-pull', function () {
    app(PageImporter::class)->importSlug('home');
    $page = Page::where('slug', 'home')->first();
    $page->update(['title' => 'Edited In Editor']);

    app(PageImporter::class)->syncNew(); // pull

    expect($page->fresh()->title)->toBe('Edited In Editor'); // untouched
});

test('non-admins cannot pull pages', function () {
    $this->actingAs(User::factory()->create(['user_type' => 'user']))
        ->post(route('pages.pull'))
        ->assertForbidden();
});
