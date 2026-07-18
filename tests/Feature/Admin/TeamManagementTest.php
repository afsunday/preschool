<?php

use App\Models\Classroom;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    // The permission catalogue (drives the `exists` rule + the index groups).
    $this->seed(PermissionSeeder::class);

    $this->owner = User::factory()->admin()->create(); // super by default
});

// ---- access scoping --------------------------------------------------------

test('a super admin passes every permission gate', function () {
    $this->actingAs($this->owner)->get('/admin/team')->assertOk();
    $this->actingAs($this->owner)->get('/admin/materials')->assertOk();
    $this->actingAs($this->owner)->get('/admin/newsletter')->assertOk();
});

test('a scoped admin only reaches their permitted areas', function () {
    $staff = User::factory()->admin()->create([
        'is_super' => false,
        'permissions' => ['content.resources'],
    ]);

    $this->actingAs($staff)->get('/admin/materials')->assertOk();
    $this->actingAs($staff)->get('/admin/newsletter')->assertForbidden();
    $this->actingAs($staff)->get('/admin/team')->assertForbidden();
});

test('an admin with no permissions is blocked from every gated area', function () {
    $staff = User::factory()->admin()->create([
        'is_super' => false,
        'permissions' => [],
    ]);

    $this->actingAs($staff)->get('/admin/materials')->assertForbidden();
    $this->actingAs($staff)->get('/admin/team')->assertForbidden();
});

test('a teacher without back-office access cannot reach the admin', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)->get('/admin/team')->assertForbidden();
});

// ---- managing the team -----------------------------------------------------

test('an admin can add a scoped back-office member', function () {
    $this->actingAs($this->owner)->post('/admin/team', [
        'first_name' => 'Nina',
        'last_name' => 'Okafor',
        'email' => 'nina@example.com',
        'password' => 'secret-pass',
        'has_admin_access' => true,
        'permissions' => ['content.resources', 'comms.messages'],
    ])->assertRedirect();

    $member = User::firstWhere('email', 'nina@example.com');

    expect($member)->not->toBeNull()
        ->and($member->user_type)->toBe('staff')
        ->and($member->has_admin_access)->toBeTrue()
        ->and($member->is_super)->toBeFalse()
        ->and($member->permissions)->toBe(['content.resources', 'comms.messages'])
        ->and($member->email_verified_at)->not->toBeNull()
        ->and($member->hasPermission('content.resources'))->toBeTrue()
        ->and($member->hasPermission('comms.newsletter'))->toBeFalse();
});

test('an admin can add a plain teacher with no back-office access', function () {
    $this->actingAs($this->owner)->post('/admin/team', [
        'first_name' => 'James',
        'last_name' => 'Bright',
        'email' => 'james@example.com',
        'password' => 'secret-pass',
    ])->assertRedirect();

    $teacher = User::firstWhere('email', 'james@example.com');

    expect($teacher)->not->toBeNull()
        ->and($teacher->user_type)->toBe('staff')
        ->and($teacher->has_admin_access)->toBeFalse()
        ->and($teacher->is_super)->toBeFalse()
        ->and($teacher->permissions)->toBe([])
        ->and($teacher->email_verified_at)->not->toBeNull();
});

test('full access sets super and clears the scoped list', function () {
    $this->actingAs($this->owner)->post('/admin/team', [
        'first_name' => 'Owner',
        'email' => 'owner2@example.com',
        'password' => 'secret-pass',
        'has_admin_access' => true,
        'is_super' => true,
        'permissions' => ['content.resources'],
    ])->assertRedirect();

    $member = User::firstWhere('email', 'owner2@example.com');

    expect($member->is_super)->toBeTrue()
        ->and($member->permissions)->toBe([]);
});

test('permissions posted without back-office access are ignored', function () {
    $this->actingAs($this->owner)->post('/admin/team', [
        'first_name' => 'Sly',
        'email' => 'sly@example.com',
        'password' => 'secret-pass',
        'has_admin_access' => false,
        'permissions' => ['content.resources'],
    ])->assertRedirect();

    $member = User::firstWhere('email', 'sly@example.com');

    expect($member->has_admin_access)->toBeFalse()
        ->and($member->permissions)->toBe([]);
});

test('an unknown permission is rejected', function () {
    $this->actingAs($this->owner)->post('/admin/team', [
        'first_name' => 'Nina',
        'email' => 'nina@example.com',
        'password' => 'secret-pass',
        'has_admin_access' => true,
        'permissions' => ['content.resources', 'not.a.permission'],
    ])->assertSessionHasErrors('permissions.1');

    expect(User::where('email', 'nina@example.com')->exists())->toBeFalse();
});

test('an admin can change a member\'s permissions', function () {
    $member = User::factory()->admin()->create([
        'is_super' => false,
        'permissions' => ['content.resources'],
    ]);

    $this->actingAs($this->owner)->put("/admin/team/{$member->id}", [
        'first_name' => $member->first_name,
        'email' => $member->email,
        'has_admin_access' => true,
        'permissions' => ['comms.newsletter'],
    ])->assertRedirect();

    expect($member->fresh()->permissions)->toBe(['comms.newsletter']);
});

test('revoking back-office access drops a member to a plain teacher', function () {
    $member = User::factory()->admin()->create([
        'is_super' => false,
        'permissions' => ['content.resources'],
    ]);

    $this->actingAs($this->owner)->put("/admin/team/{$member->id}", [
        'first_name' => $member->first_name,
        'email' => $member->email,
        'has_admin_access' => false,
    ])->assertRedirect();

    $fresh = $member->fresh();

    expect($fresh->has_admin_access)->toBeFalse()
        ->and($fresh->permissions)->toBe([])
        ->and($fresh->isStaff())->toBeTrue();
});

test('an admin can remove a member', function () {
    $member = User::factory()->teacher()->create();

    $this->actingAs($this->owner)->delete("/admin/team/{$member->id}")->assertRedirect();

    expect(User::find($member->id))->toBeNull();
});

test('removing a member leaves their rooms unassigned', function () {
    $teacher = User::factory()->teacher()->create();
    $room = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $this->actingAs($this->owner)->delete("/admin/team/{$teacher->id}")->assertRedirect();

    expect(User::find($teacher->id))->toBeNull()
        ->and($room->fresh()->teacher_id)->toBeNull();
});

test('you cannot delete yourself', function () {
    $this->actingAs($this->owner)->delete("/admin/team/{$this->owner->id}")->assertForbidden();

    expect(User::find($this->owner->id))->not->toBeNull();
});

test('the team endpoint only edits staff', function () {
    $parent = User::factory()->parent()->create();

    $this->actingAs($this->owner)
        ->put("/admin/team/{$parent->id}", ['first_name' => 'X', 'email' => $parent->email])
        ->assertNotFound();
});

test('a member without team.staff cannot create team members', function () {
    $staff = User::factory()->admin()->create([
        'is_super' => false,
        'permissions' => ['content.resources'],
    ]);

    $this->actingAs($staff)->post('/admin/team', [
        'first_name' => 'X',
        'email' => 'x@example.com',
        'password' => 'secret-pass',
    ])->assertForbidden();
});
