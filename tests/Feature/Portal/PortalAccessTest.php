<?php

use App\Models\Child;
use App\Models\Classroom;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Who can see which room. A parent reaches a class only through their own child;
 * a teacher only through the room they're assigned to.
 */
beforeEach(function () {
    $this->teacher = User::factory()->teacher()->create();
    $this->classroom = Classroom::factory()->create(['teacher_id' => $this->teacher->id]);

    $this->parent = User::factory()->parent()->create();
    $this->child = Child::factory()->create(['classroom_id' => $this->classroom->id]);
    $this->child->guardians()->attach($this->parent->id, ['relationship' => 'mum']);
});

test('a teacher sees the room they run', function () {
    $this->actingAs($this->teacher)
        ->get(route('portal.classes.feed', $this->classroom))
        ->assertOk()
        ->assertInertia(fn (Assert $p) => $p
            ->component('portal/class/feed')
            ->where('canPost', true));
});

test('a parent reaches the room through their child but cannot post', function () {
    $this->actingAs($this->parent)
        ->get(route('portal.classes.feed', $this->classroom))
        ->assertOk()
        ->assertInertia(fn (Assert $p) => $p->where('canPost', false));
});

test('a teacher from another room is locked out', function () {
    $other = User::factory()->teacher()->create();

    $this->actingAs($other)
        ->get(route('portal.classes.feed', $this->classroom))
        ->assertForbidden();
});

test('a parent with no child in the room is locked out', function () {
    $stranger = User::factory()->parent()->create();

    $this->actingAs($stranger)
        ->get(route('portal.classes.feed', $this->classroom))
        ->assertForbidden();
});

test('an admin sees every room', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('portal.classes.feed', $this->classroom))
        ->assertOk()
        ->assertInertia(fn (Assert $p) => $p->where('canPost', true));
});

test('home lists only the classes a user may see', function () {
    Classroom::factory()->create(); // someone else's room

    $this->actingAs($this->teacher)
        ->get(route('portal.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $p) => $p
            ->component('portal/home')
            ->has('classes', 1)
            ->where('classes.0.id', $this->classroom->id));
});

test('one parent with two children sees both rooms', function () {
    $second = Classroom::factory()->create();
    $sibling = Child::factory()->create(['classroom_id' => $second->id]);
    $sibling->guardians()->attach($this->parent->id, ['relationship' => 'mum']);

    $this->actingAs($this->parent)
        ->get(route('portal.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $p) => $p
            ->has('classes', 2)
            ->has('children', 2));
});

test('guests are redirected to login', function () {
    $this->get(route('portal.home'))->assertRedirect(route('login'));
});
