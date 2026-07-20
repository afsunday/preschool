<?php

use App\Models\Child;
use App\Models\Classroom;
use App\Models\User;

function staffUser(): User
{
    return User::factory()->create(['user_type' => User::STAFF]);
}

test('staff can view the students directory', function () {
    $this->actingAs(staffUser())
        ->get('/portal/students')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('portal/students/index'));
});

test('a parent cannot view the students directory', function () {
    $this->actingAs(User::factory()->parent()->create())
        ->get('/portal/students')
        ->assertForbidden();
});

test('staff can add a student, and it gets an invite code', function () {
    $this->actingAs(staffUser())
        ->post('/portal/students', [
            'first_name' => 'Ada',
            'last_name' => 'Okafor',
        ])
        ->assertRedirect();

    $child = Child::where('first_name', 'Ada')->firstOrFail();
    expect($child->invite_code)->not->toBeNull();
    expect($child->classroom_id)->toBeNull();
});

test('a new student can be enrolled into a class on creation', function () {
    $room = Classroom::factory()->create();

    $this->actingAs(staffUser())
        ->post('/portal/students', [
            'first_name' => 'Ben',
            'last_name' => 'Ade',
            'classroom_id' => $room->id,
        ])
        ->assertRedirect();

    $child = Child::where('first_name', 'Ben')->firstOrFail();
    expect($child->classroom_id)->toBe($room->id);
    expect($child->enrollments()->whereNull('ended_on')->count())->toBe(1);
});

test('moving a student to a new class keeps the old class in history', function () {
    $a = Classroom::factory()->create();
    $b = Classroom::factory()->create();
    $child = Child::factory()->create(['classroom_id' => null]);
    $staff = staffUser();

    $this->actingAs($staff)
        ->post("/portal/students/{$child->id}/enroll", ['classroom_id' => $a->id])
        ->assertRedirect();
    $this->actingAs($staff)
        ->post("/portal/students/{$child->id}/enroll", ['classroom_id' => $b->id])
        ->assertRedirect();

    $child->refresh();
    expect($child->classroom_id)->toBe($b->id);
    expect($child->enrollments()->count())->toBe(2);
    expect($child->enrollments()->whereNull('ended_on')->count())->toBe(1);
    expect($child->enrollments()->whereNotNull('ended_on')->first()->classroom_id)
        ->toBe($a->id);
});

test('a placement made before the enrolment log is captured on first move', function () {
    $a = Classroom::factory()->create();
    $b = Classroom::factory()->create();
    // Legacy child: has a current room but no enrolment rows.
    $child = Child::factory()->create(['classroom_id' => $a->id]);
    expect($child->enrollments()->count())->toBe(0);

    $child->enrollInto($b);
    $child->refresh();

    expect($child->classroom_id)->toBe($b->id);
    expect($child->enrollments()->count())->toBe(2);
    expect($child->enrollments()->whereNotNull('ended_on')->first()->classroom_id)
        ->toBe($a->id);
});

test('a parent cannot add or enrol students', function () {
    $parent = User::factory()->parent()->create();
    $child = Child::factory()->create();

    $this->actingAs($parent)
        ->post('/portal/students', ['first_name' => 'X', 'last_name' => 'Y'])
        ->assertForbidden();

    $this->actingAs($parent)
        ->post("/portal/students/{$child->id}/enroll", [
            'classroom_id' => Classroom::factory()->create()->id,
        ])
        ->assertForbidden();
});
