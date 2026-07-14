<?php

use App\Models\User;

test('guests are redirected to login from the media library', function () {
    $this->get(route('media.index'))->assertRedirect(route('login'));
});

test('non-admin users are forbidden from the media library', function () {
    $this->actingAs(User::factory()->create(['user_type' => 'user']));

    $this->get(route('media.index'))->assertForbidden();
});

test('admin users can visit the media library', function () {
    $this->actingAs(User::factory()->create(['user_type' => 'admin']));

    $this->get(route('media.index'))->assertOk();
});
