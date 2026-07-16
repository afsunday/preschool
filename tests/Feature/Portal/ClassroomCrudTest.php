<?php

use App\Models\Classroom;
use App\Models\Media;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('an admin creates a class and lands in it', function () {
    $this->actingAs($this->admin)
        ->post(route('portal.classes.store'), [
            'name' => 'Mr James',
            'grade' => 'Grade 1',
            'year' => '2026/2027',
        ])
        ->assertRedirect();

    $classroom = Classroom::firstWhere('name', 'Mr James');

    expect($classroom)->not->toBeNull()
        ->and($classroom->label)->toBe('Mr James · Grade 1 · 2026/2027');
});

test('a class banner is attached through the mediables pivot', function () {
    $image = Media::factory()->create();

    $this->actingAs($this->admin)->post(route('portal.classes.store'), [
        'name' => 'Mr James',
        'year' => '2026/2027',
        'banner_media_id' => $image->id,
    ]);

    $classroom = Classroom::firstWhere('name', 'Mr James');

    expect($classroom->banner()?->id)->toBe($image->id);
});

test('changing the banner replaces it rather than stacking', function () {
    $first = Media::factory()->create();
    $second = Media::factory()->create();

    $classroom = Classroom::factory()->create();
    $classroom->setBanner($first->id);
    $classroom->setBanner($second->id);

    expect($classroom->banner()?->id)->toBe($second->id)
        ->and($classroom->getMedia(Classroom::BANNER))->toHaveCount(1);
});

test('a class can have no banner', function () {
    $classroom = Classroom::factory()->create();
    $classroom->setBanner(Media::factory()->create()->id);
    $classroom->setBanner(null);

    expect($classroom->banner())->toBeNull();
});

test('a renaming update leaves the banner alone', function () {
    $image = Media::factory()->create();
    $classroom = Classroom::factory()->create();
    $classroom->setBanner($image->id);

    $this->actingAs($this->admin)->patch(route('portal.classes.update', $classroom), [
        'name' => 'Renamed',
        'year' => '2026/2027',
    ]);

    expect($classroom->fresh()->banner()?->id)->toBe($image->id);
});

test('an unknown banner id is rejected', function () {
    $this->actingAs($this->admin)
        ->post(route('portal.classes.store'), [
            'name' => 'Rogue',
            'year' => '2026/2027',
            'banner_media_id' => 99999,
        ])
        ->assertSessionHasErrors('banner_media_id');

    expect(Classroom::where('name', 'Rogue')->exists())->toBeFalse();
});

test('a teacher cannot create a class', function () {
    $this->actingAs(User::factory()->teacher()->create())
        ->post(route('portal.classes.store'), [
            'name' => 'Mine',
            'year' => '2026/2027',
        ])
        ->assertForbidden();
});

test('a parent cannot create a class', function () {
    $this->actingAs(User::factory()->parent()->create())
        ->post(route('portal.classes.store'), [
            'name' => 'Mine',
            'year' => '2026/2027',
        ])
        ->assertForbidden();
});

test('a class can only be assigned to staff', function () {
    $parent = User::factory()->parent()->create();

    $this->actingAs($this->admin)
        ->post(route('portal.classes.store'), [
            'name' => 'Mr James',
            'year' => '2026/2027',
            'teacher_id' => $parent->id,
        ])
        ->assertSessionHasErrors('teacher_id');
});

test('deleting a class archives it rather than destroying the year', function () {
    $classroom = Classroom::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('portal.classes.destroy', $classroom))
        ->assertRedirect(route('portal.home'));

    expect($classroom->fresh()->is_archived)->toBeTrue();

    // …and it drops out of the switcher.
    $this->actingAs($this->admin)
        ->get(route('portal.home'))
        ->assertInertia(fn ($p) => $p->has('classes', 0));
});

test('home exposes the teacher list to admins only', function () {
    User::factory()->teacher()->create();

    // Admins are assignable too — they cover rooms — so the list is teacher + admin.
    $this->actingAs($this->admin)
        ->get(route('portal.home'))
        ->assertInertia(fn ($p) => $p->where('canCreate', true)->has('teachers', 2));

    $this->actingAs(User::factory()->teacher()->create())
        ->get(route('portal.home'))
        ->assertInertia(fn ($p) => $p->where('canCreate', false)->has('teachers', 0));
});
