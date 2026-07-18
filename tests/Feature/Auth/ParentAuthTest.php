<?php

use App\Models\User;

test('registration creates a parent account', function () {
    $this->post(route('register.store'), [
        'first_name' => 'Ada',
        'last_name' => 'Parent',
        'email' => 'ada@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::firstWhere('email', 'ada@example.com');

    expect($user)->not->toBeNull()
        ->and($user->user_type)->toBe('parent')
        ->and($user->isStaff())->toBeFalse()
        ->and($user->isAdmin())->toBeFalse();
});

test('a parent lands in the portal after login', function () {
    $parent = User::factory()->parent()->create();

    $this->post(route('login.store'), [
        'email' => $parent->email,
        'password' => 'password',
    ])->assertRedirect(route('portal.home', absolute: false));
});

test('an admin lands on the dashboard after login', function () {
    $admin = User::factory()->admin()->create();

    $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));
});

test('a teacher lands in the portal after login', function () {
    $teacher = User::factory()->teacher()->create();

    $this->post(route('login.store'), [
        'email' => $teacher->email,
        'password' => 'password',
    ])->assertRedirect(route('portal.home', absolute: false));
});
