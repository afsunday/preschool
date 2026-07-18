<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('an admin can visit the dashboard', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('a non-admin is bounced from the dashboard to the portal', function () {
    $user = User::factory()->parent()->create();

    $this->actingAs($user)->get(route('dashboard'))->assertRedirect('/portal');
});
