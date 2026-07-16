<?php

use App\Models\Child;
use App\Models\Classroom;
use App\Models\User;

/**
 * Linking a parent to a child. The invite code is the entire relationship
 * system, so these are the tests that matter most.
 */
beforeEach(function () {
    $this->classroom = Classroom::factory()->create();
    $this->child = Child::factory()->create([
        'classroom_id' => $this->classroom->id,
        'invite_code' => 'TUNDE001',
    ]);
});

test('redeeming an invite code links the parent to the child', function () {
    $user = User::factory()->create(['user_type' => 'user']);

    $this->actingAs($user)
        ->post(route('portal.join.store'), [
            'code' => 'TUNDE001',
            'relationship' => 'mum',
        ])
        ->assertRedirect(route('portal.classes.today', $this->classroom->id));

    expect($user->fresh()->children)->toHaveCount(1)
        ->and($this->child->guardians()->first()->id)->toBe($user->id)
        ->and($this->child->guardians()->first()->pivot->relationship)->toBe('mum');
});

test('redeeming a code makes a plain user a parent', function () {
    $user = User::factory()->create(['user_type' => 'user']);

    $this->actingAs($user)->post(route('portal.join.store'), [
        'code' => 'TUNDE001',
        'relationship' => 'dad',
    ]);

    expect($user->fresh()->isParent())->toBeTrue();
});

test('a bad code is rejected and links nothing', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('portal.join.store'), [
            'code' => 'NOPE0000',
            'relationship' => 'mum',
        ])
        ->assertSessionHasErrors('code');

    expect($user->fresh()->children)->toHaveCount(0);
});

test('the code is case-insensitive', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('portal.join.store'), [
        'code' => 'tunde001',
        'relationship' => 'guardian',
    ]);

    expect($user->fresh()->children)->toHaveCount(1);
});

test('one parent can redeem several codes — one per child', function () {
    $sibling = Child::factory()->create(['invite_code' => 'ZARA0001']);
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('portal.join.store'), ['code' => 'TUNDE001', 'relationship' => 'mum']);
    $this->actingAs($user)->post(route('portal.join.store'), ['code' => 'ZARA0001', 'relationship' => 'mum']);

    expect($user->fresh()->children->pluck('id'))
        ->toContain($this->child->id, $sibling->id)
        ->toHaveCount(2);
});

test('both parents of one child can redeem the same code', function () {
    $mum = User::factory()->create();
    $dad = User::factory()->create();

    $this->actingAs($mum)->post(route('portal.join.store'), ['code' => 'TUNDE001', 'relationship' => 'mum']);
    $this->actingAs($dad)->post(route('portal.join.store'), ['code' => 'TUNDE001', 'relationship' => 'dad']);

    expect($this->child->guardians()->count())->toBe(2);
});

test('redeeming twice does not duplicate the link', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('portal.join.store'), ['code' => 'TUNDE001', 'relationship' => 'mum']);
    $this->actingAs($user)->post(route('portal.join.store'), ['code' => 'TUNDE001', 'relationship' => 'mum']);

    expect($user->fresh()->children)->toHaveCount(1);
});

test('redeeming a code grants access to that child\'s room', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('portal.classes.feed', $this->classroom))->assertForbidden();

    $this->actingAs($user)->post(route('portal.join.store'), ['code' => 'TUNDE001', 'relationship' => 'mum']);

    $this->actingAs($user)->get(route('portal.classes.feed', $this->classroom))->assertOk();
});

test('the invite code is only exposed to admins', function () {
    $parent = User::factory()->parent()->create();
    $this->child->guardians()->attach($parent->id, ['relationship' => 'mum']);

    $this->actingAs($parent)
        ->get(route('portal.classes.children', $this->classroom))
        ->assertOk()
        ->assertInertia(fn ($p) => $p->where('children.0.inviteCode', null));

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('portal.classes.children', $this->classroom))
        ->assertOk()
        ->assertInertia(fn ($p) => $p->where('children.0.inviteCode', 'TUNDE001'));
});
