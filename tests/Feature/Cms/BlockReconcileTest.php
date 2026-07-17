<?php

use App\Cms\BlockType;
use App\Cms\PageImporter;
use App\Models\Page;
use App\Models\PageBlock;

/**
 * `pull` reconcile: bring an existing block's settings up to the current schema
 * without overwriting content the builder owns.
 *
 * Structural, not semantic — the *set of keys* is made current (new fields
 * seeded from the blueprint, removed fields pruned, repeater rows reshaped),
 * but an edited value is never touched. Renames are out of scope by design.
 */

// A stand-in block type so the logic can be exercised without a blueprint on
// disk: a title, a subtitle, and a stats repeater of (value, label).
function demoType(int $version = 1): BlockType
{
    return new BlockType(
        key: 'demo',
        name: 'Demo',
        group: 'Content',
        version: $version,
        acceptsChildren: false,
        fieldSpecs: [
            ['id' => 'title', 'type' => 'text'],
            ['id' => 'subtitle', 'type' => 'text'],
            ['id' => 'stats', 'type' => 'repeater', 'fields' => [
                ['id' => 'value', 'type' => 'text'],
                ['id' => 'label', 'type' => 'text'],
            ]],
        ],
    );
}

// ---- the reconcile rules (no DB, no disk) ----------------------------------

test('an edited value always wins over the blueprint', function () {
    $after = demoType()->reconcile(
        stored: ['title' => 'my edit'],
        seed: ['title' => 'blueprint copy'],
    );

    expect($after['title'])->toBe('my edit');
});

test('a brand-new field is seeded from the blueprint', function () {
    // The block predates the `subtitle` field; the blueprint carries the copy.
    $after = demoType()->reconcile(
        stored: ['title' => 'kept'],
        seed: ['title' => 'ignored', 'subtitle' => 'from the design'],
    );

    expect($after['subtitle'])->toBe('from the design')
        ->and($after['title'])->toBe('kept');
});

test('a field the schema dropped is pruned', function () {
    $after = demoType()->reconcile(
        stored: ['title' => 'kept', 'legacy' => 'gone'],
        seed: [],
    );

    expect($after)->toHaveKey('title')->not->toHaveKey('legacy');
});

test('a field absent from both stays absent — nothing invents a default', function () {
    // The view falls back at render via get($key, …); an empty key is not stored.
    $after = demoType()->reconcile(stored: ['title' => 'x'], seed: []);

    expect($after)->not->toHaveKey('subtitle');
});

test('a repeater keeps its rows and only loses removed sub-keys', function () {
    $after = demoType()->reconcile(
        stored: ['stats' => [
            ['value' => '7.5K+', 'label' => 'students', 'icon' => 'legacy'],
            ['value' => '12', 'label' => 'teachers'],
        ]],
        seed: [],
    );

    // Count and order untouched; the undeclared `icon` pruned; content kept.
    expect($after['stats'])->toBe([
        ['value' => '7.5K+', 'label' => 'students'],
        ['value' => '12', 'label' => 'teachers'],
    ]);
});

test('a new repeater sub-key is left absent, not invented per row', function () {
    // Rows have no stable identity, so there is nothing to seed a new sub-key
    // from — you fill it in the builder.
    $after = demoType()->reconcile(
        stored: ['stats' => [['value' => 'only value']]],
        seed: [],
    );

    expect($after['stats'][0])->toBe(['value' => 'only value']);
});

test('a repeater the block lacks is seeded whole from the blueprint', function () {
    $after = demoType()->reconcile(
        stored: ['title' => 'x'],
        seed: ['stats' => [['value' => 'a', 'label' => 'b']]],
    );

    expect($after['stats'])->toBe([['value' => 'a', 'label' => 'b']]);
});

test('an edited repeater wins over the blueprint wholesale — never merged', function () {
    $after = demoType()->reconcile(
        stored: ['stats' => [['value' => '1'], ['value' => '2'], ['value' => '3']]],
        seed: ['stats' => [['value' => 'seed', 'label' => 'seed']]],
    );

    expect($after['stats'])->toHaveCount(3);
});

// ---- reconcileBlocks over a real page --------------------------------------

beforeEach(function () {
    $this->importer = app(PageImporter::class);
    $this->importer->importSlug('home');
    $this->page = Page::firstWhere('slug', 'home');
    $this->hero = fn (): PageBlock => $this->page->allBlocks()->where('key', 'hero')->firstOrFail();

    // The blueprint's own hero settings, to assert seeded values against.
    $this->blueprintHero = collect(
        json_decode((string) file_get_contents(resource_path('cms/pages/home.json')), true)['blocks']
    )->firstWhere('key', 'hero')['settings'];
});

test('reconciling a freshly imported page changes nothing', function () {
    // Settings are written in schema order by both import and reconcile, so an
    // up-to-date page compares identical — no churn.
    expect($this->importer->reconcileBlocks('home'))->toBe([]);
});

test('reconcile seeds a field the block was missing from the blueprint', function () {
    // Simulate a hero that predates the `subtitle` field.
    $hero = ($this->hero)();
    $settings = $hero->settings;
    unset($settings['subtitle']);
    $hero->update(['settings' => $settings]);

    $changed = $this->importer->reconcileBlocks('home');

    expect($changed)->toContain('hero');
    expect(($this->hero)()->settings['subtitle'])
        ->toBe($this->blueprintHero['subtitle']);
});

test('reconcile never overwrites an edited value', function () {
    ($this->hero)()->update(['settings' => [
        ...($this->hero)()->settings,
        'title' => 'EDITED IN THE BUILDER',
    ]]);

    $this->importer->reconcileBlocks('home');

    expect(($this->hero)()->settings['title'])->toBe('EDITED IN THE BUILDER');
});

test('reconcile prunes a stale key and lifts the schema version', function () {
    $hero = ($this->hero)();
    $hero->update([
        'settings' => [...$hero->settings, 'legacy_field' => 'remove me'],
        'schema_version' => 0,
    ]);

    $changed = $this->importer->reconcileBlocks('home');

    $fresh = ($this->hero)();
    expect($changed)->toContain('hero');
    expect($fresh->settings)->not->toHaveKey('legacy_field');
    expect($fresh->schema_version)->toBeGreaterThan(0);
});
