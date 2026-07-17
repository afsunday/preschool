<?php

use App\Cms\BlockTypeRegistry;
use App\Cms\PageImporter;
use App\Models\Page;
use App\Models\User;
use Illuminate\Database\QueryException;

/**
 * `pull` after the first import: add blueprint blocks the page hasn't got,
 * matched on `key`, and touch nothing else.
 *
 * A blueprint is a seed, not a live config — the editor owns the content.
 */
beforeEach(function () {
    $this->importer = app(PageImporter::class);
    $this->importer->importSlug('home');
    $this->page = Page::firstWhere('slug', 'home');
});

test('every imported block carries its blueprint key', function () {
    // Without a key, sync would have to guess by type — which breaks the moment
    // a page legitimately has two blocks of the same kind.
    expect($this->page->allBlocks()->whereNull('key')->count())->toBe(0);
    expect($this->page->allBlocks()->first()->key)->toBe('hero');
});

test('syncing a page that is already current adds nothing', function () {
    expect($this->importer->syncBlocks('home'))->toBe([]);
});

test('a block missing from the page is appended, and lands last', function () {
    $hero = $this->page->allBlocks()->where('key', 'hero')->first();
    $hero->delete();

    $added = $this->importer->syncBlocks('home');

    expect($added)->toBe(['hero']);

    $blocks = $this->page->fresh()->allBlocks()->orderBy('position')->get();

    // Appended, not restored to index 0: once the page is edited the two orders
    // have diverged, so the blueprint's position would be asserting something
    // it cannot know.
    expect($blocks->last()->key)->toBe('hero');
});

test('sync never overwrites an edited block', function () {
    $hero = $this->page->allBlocks()->where('key', 'hero')->first();
    $hero->update(['settings' => ['title' => 'EDITED IN THE BUILDER']]);

    $this->importer->syncBlocks('home');

    expect($hero->fresh()->settings['title'])->toBe('EDITED IN THE BUILDER');
});

test('sync never touches a block added in the editor', function () {
    // Editor-added blocks have no key, so the blueprint cannot claim them.
    $mine = $this->page->allBlocks()->create([
        'type' => 'home_hero',
        'key' => null,
        'name' => 'my extra hero',
        'position' => 99,
        'settings' => ['title' => 'Mine'],
    ]);

    $this->importer->syncBlocks('home');

    expect($mine->fresh())->not->toBeNull()
        ->and($mine->fresh()->settings['title'])->toBe('Mine');
});

test('sync does not duplicate when the page has two blocks of one type', function () {
    $this->page->allBlocks()->create([
        'type' => 'home_hero', 'key' => null, 'position' => 99, 'settings' => [],
    ]);

    expect($this->importer->syncBlocks('home'))->toBe([]);
    expect($this->page->fresh()->allBlocks()->where('type', 'home_hero')->count())->toBe(2);
});

test('a key is unique within a page', function () {
    expect(fn () => $this->page->allBlocks()->create([
        'type' => 'home_hero', 'key' => 'hero', 'position' => 99, 'settings' => [],
    ]))->toThrow(QueryException::class);
});

// ---- scoping ---------------------------------------------------------------

test('a page is only offered block types it can actually render', function () {
    // The markup lives in the page's own view, so a global catalogue would offer
    // blocks that render as nothing.
    $registry = app(BlockTypeRegistry::class);

    $home = array_keys($registry->forPage('home'));
    $contact = array_keys($registry->forPage('contact'));

    expect($home)->toContain('home_hero')
        ->and($contact)->not->toContain('home_hero')
        ->and(count($contact))->toBeLessThan(count($registry->all()));
});

test('every page is offered the globals', function (string $slug) {
    // Chrome is available everywhere, which is what replaces `shared.json`.
    expect(array_keys(app(BlockTypeRegistry::class)->forPage($slug)))
        ->toContain('newsletter');
})->with(['home', 'contact', 'faq']);

test('the editor endpoint serves only that page scope', function () {
    $admin = User::factory()->create(['user_type' => 'admin']);
    $contact = Page::firstWhere('slug', 'contact') ?? $this->importer->importSlug('contact');

    $keys = collect($this->actingAs($admin)
        ->getJson(route('builder.schema', $contact))
        ->assertOk()
        ->json('data'))->pluck('key');

    expect($keys)->toContain('newsletter')
        ->and($keys)->not->toContain('home_hero');
});
