<?php

use App\Models\Page;

test('the homepage renders from the cms', function () {
    $page = Page::factory()->create(['slug' => 'home', 'title' => 'Home']);
    $page->allSections()->create([
        'type' => 'home_hero',
        'position' => 0,
        'is_visible' => true,
        'settings' => ['title' => 'Where little minds come alive'],
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Where little minds come alive');
});
