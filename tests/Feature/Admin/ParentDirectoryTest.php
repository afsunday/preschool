<?php

use App\Models\Child;
use App\Models\Classroom;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
    $this->owner = User::factory()->admin()->create(); // super by default
});

test('an admin can see the parents directory', function () {
    $this->actingAs($this->owner)->get('/admin/parents')->assertOk();
});

test('the directory lists parents with their linked children', function () {
    $room = Classroom::factory()->create(['name' => 'Mr James']);
    $child = Child::factory()->create([
        'classroom_id' => $room->id,
        'first_name' => 'Tunde',
    ]);
    $parent = User::factory()->parent()->create(['first_name' => 'Bisi', 'last_name' => 'Adeyemi']);
    $child->guardians()->attach($parent->id, ['relationship' => 'mum']);

    $this->actingAs($this->owner)
        ->get('/admin/parents')
        ->assertInertia(fn ($p) => $p
            ->has('parents', 1)
            ->where('parents.0.name', 'Bisi Adeyemi')
            ->where('parents.0.children.0.name', fn ($name) => str_contains($name, 'Tunde'))
            ->where('parents.0.children.0.classroom', fn ($label) => str_contains($label, 'Mr James'))
        );
});

test('staff accounts are not listed as parents', function () {
    User::factory()->teacher()->create();
    User::factory()->admin()->create(['is_super' => false]);
    User::factory()->parent()->create();

    $this->actingAs($this->owner)
        ->get('/admin/parents')
        ->assertInertia(fn ($p) => $p->has('parents', 1));
});

test('a scoped admin without parents.view is blocked', function () {
    $staff = User::factory()->admin()->create([
        'is_super' => false,
        'permissions' => ['content.resources'],
    ]);

    $this->actingAs($staff)->get('/admin/parents')->assertForbidden();
});

test('a teacher cannot reach the parents directory', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)->get('/admin/parents')->assertForbidden();
});

test('a parent cannot reach the parents directory', function () {
    $parent = User::factory()->parent()->create();

    $this->actingAs($parent)->get('/admin/parents')->assertForbidden();
});
