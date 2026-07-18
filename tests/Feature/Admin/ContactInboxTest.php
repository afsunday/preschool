<?php

use App\Models\ContactSubmission;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['user_type' => 'admin']);
});

test('an admin sees the messages inbox', function () {
    ContactSubmission::factory()->create();

    $this->actingAs($this->admin)->get('/admin/messages')->assertOk();
});

test('a non-admin cannot reach the inbox', function () {
    $user = User::factory()->create(['user_type' => 'user']);

    $this->actingAs($user)->get('/admin/messages')->assertForbidden();
});

test('marking a message toggles its read state', function () {
    $message = ContactSubmission::factory()->create(['is_read' => false]);

    $this->actingAs($this->admin)->patch("/admin/messages/{$message->id}")->assertRedirect();
    expect($message->fresh()->is_read)->toBeTrue();

    $this->actingAs($this->admin)->patch("/admin/messages/{$message->id}");
    expect($message->fresh()->is_read)->toBeFalse();
});

test('an admin can delete a message', function () {
    $message = ContactSubmission::factory()->create();

    $this->actingAs($this->admin)->delete("/admin/messages/{$message->id}")->assertRedirect();

    expect(ContactSubmission::count())->toBe(0);
});
