<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('a parent can view their portal settings', function () {
    $parent = User::factory()->parent()->create();

    $this->actingAs($parent)->get('/portal/settings')->assertOk();
});

test('a parent updates their profile from the portal and stays in the portal', function () {
    $parent = User::factory()->parent()->create();

    $this->actingAs($parent)
        ->from('/portal/settings')
        ->patch(route('profile.update'), [
            'first_name' => 'Ada',
            'last_name' => 'Bright',
            'email' => 'ada.bright@example.com',
        ])
        ->assertRedirect('/portal/settings');

    $parent->refresh();

    expect($parent->first_name)->toBe('Ada')
        ->and($parent->email)->toBe('ada.bright@example.com');
});

test('a parent changes their password from the portal', function () {
    $parent = User::factory()->parent()->create();

    $this->actingAs($parent)
        ->from('/portal/settings')
        ->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/portal/settings');

    expect(Hash::check('new-secret-password', $parent->refresh()->password))->toBeTrue();
});
