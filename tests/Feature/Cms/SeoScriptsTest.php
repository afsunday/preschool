<?php

use App\Models\Page;
use App\Models\User;

test('saving stores seo meta + header/footer scripts', function () {
    $admin = User::factory()->create(['user_type' => 'admin']);
    $page = Page::factory()->create();

    $this->actingAs($admin)->putJson(route('builder.save', $page), [
        'title' => 'T',
        'status' => 'published',
        'meta' => ['title' => 'Best Daycare', 'description' => 'We care'],
        'headerScripts' => '<meta name="x" content="1">',
        'footerScripts' => '<script>window.chat=1</script>',
        'sections' => [],
    ])->assertOk()
        ->assertJsonPath('data.headerScripts', '<meta name="x" content="1">')
        ->assertJsonPath('data.meta.title', 'Best Daycare');

    expect($page->fresh()->footer_scripts)->toBe('<script>window.chat=1</script>');
});

test('the public page injects the scripts and meta', function () {
    $page = Page::factory()->create([
        'slug' => 'home',
        'meta_title' => 'Best Daycare',
        'header_scripts' => '<meta name="probe" content="head">',
        'footer_scripts' => '<script>window.probe="foot"</script>',
    ]);
    $page->allSections()->create([
        'type' => 'home_hero',
        'position' => 0,
        'is_visible' => true,
        'settings' => ['title' => 'Hi'],
        'schema_version' => 1,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('name="probe" content="head"', escape: false)
        ->assertSee('window.probe="foot"', escape: false)
        ->assertSee('Best Daycare', escape: false);
});
