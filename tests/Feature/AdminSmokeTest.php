<?php

use App\Models\Page;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->admin = User::factory()->create(['user_type' => 'admin']);
});

test('the rebuilt admin pages render their Inertia components', function (string $path, string $component) {
    $this->actingAs($this->admin)
        ->get($path)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component($component));
})->with([
    'dashboard' => ['/dashboard', 'dashboard'],
    'media' => ['/admin/media', 'media/index'],
    'profile' => ['/settings/profile', 'settings/profile'],
    'security' => ['/settings/security', 'settings/security'],
]);

test('login page renders for guests', function () {
    $this->get('/login')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('auth/login'));
});

test('the page editor screen renders', function () {
    $page = Page::factory()->create();

    $this->actingAs($this->admin)
        ->get("/admin/pages/{$page->id}/edit")
        ->assertOk()
        ->assertInertia(fn (Assert $p) => $p->component('cms/page-editor')->where('pageId', $page->id));
});

test('the editor preview renders the page view with selection wrappers', function () {
    // The preview renders view(slug); "home" has resources/views/home.blade.php.
    $home = Page::factory()->create(['slug' => 'home']);
    $home->allBlocks()->create([
        'type' => 'home_hero', 'position' => 0, 'is_visible' => true,
        'settings' => ['title' => 'Peek preview'], 'schema_version' => 1,
    ]);

    $this->actingAs($this->admin)
        ->get(route('builder.preview', $home))
        ->assertOk()
        ->assertSee('data-cms-block', escape: false)
        ->assertSee('Peek preview', escape: false);
});
