<?php

use App\Cms\PageImporter;
use App\Models\Page;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->admin = User::factory()->create(['user_type' => 'admin']);
});

test('the pages index lists pages', function () {
    Page::factory()->create(['title' => 'About']);

    $this->actingAs($this->admin)
        ->get(route('pages.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $p) => $p
            ->component('cms/pages-index')
            ->has('pages', 1)
            ->where('pages.0.title', 'About'));
});

test('creating a page redirects into the editor with a unique slug', function () {
    Page::factory()->create(['slug' => 'home']);

    $this->actingAs($this->admin)
        ->post(route('pages.store'), ['title' => 'Home'])
        ->assertRedirect();

    expect(Page::where('slug', 'home-2')->exists())->toBeTrue();
});

test('a page can be deleted', function () {
    $page = Page::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('pages.destroy', $page))
        ->assertRedirect(route('pages.index'));

    expect(Page::find($page->id))->toBeNull();
});

test('a system page cannot be deleted', function () {
    $page = Page::factory()->create(['is_system' => true]);

    $this->actingAs($this->admin)
        ->delete(route('pages.destroy', $page))
        ->assertRedirect(route('pages.index'));

    expect(Page::find($page->id))->not->toBeNull();
});

test('imported blueprint pages are marked as system', function () {
    app(PageImporter::class)->importSlug('about');

    expect(Page::where('slug', 'about')->firstOrFail()->is_system)->toBeTrue();
});

test('non-admins cannot reach the pages area', function () {
    $this->actingAs(User::factory()->create(['user_type' => 'user']))
        ->get(route('pages.index'))
        ->assertForbidden();
});
