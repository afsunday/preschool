<?php

use App\Models\Classroom;
use App\Models\ClassroomBanner;
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

test('a class stores a banner key from the library', function () {
    $this->actingAs($this->admin)->post(route('portal.classes.store'), [
        'name' => 'Mr James',
        'year' => '2026/2027',
        'banner' => 'xylophone',
    ]);

    expect(Classroom::firstWhere('name', 'Mr James')->banner)->toBe('xylophone');
});

test('a banner outside the library is rejected', function () {
    // Tailwind cannot scan the DB, so only library keys may ever be stored.
    foreach (['bg-[#ff0000]', 'waves-ocean', 'not-a-banner', '../../etc/passwd', 'art table'] as $bad) {
        $this->actingAs($this->admin)
            ->post(route('portal.classes.store'), [
                'name' => 'Rogue',
                'year' => '2026/2027',
                'banner' => $bad,
            ])
            ->assertSessionHasErrors('banner');
    }

    expect(Classroom::where('name', 'Rogue')->exists())->toBeFalse();
});

test('every banner in the manifest names a real file and validates', function () {
    $keys = ClassroomBanner::keys();

    expect($keys)->toHaveCount(56)
        ->and($keys)->toEqual(array_unique($keys));

    foreach ($keys as $key) {
        // The manifest is the contract between PHP and the front end; a key with
        // no artwork behind it would render a broken cover.
        expect(ClassroomBanner::valid($key))->toBeTrue()
            ->and(public_path("images/banners/{$key}.svg"))->toBeFile();
    }

    expect(ClassroomBanner::valid(ClassroomBanner::DEFAULT))->toBeTrue()
        ->and(ClassroomBanner::categories())->toHaveCount(9);
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
            'banner' => 'dots-grape',
            'teacher_ids' => [$parent->id],
        ])
        ->assertSessionHasErrors('teacher_ids.0');
});

test('a class can have more than one teacher', function () {
    $one = User::factory()->teacher()->create();
    $two = User::factory()->teacher()->create();

    $this->actingAs($this->admin)
        ->post(route('portal.classes.store'), [
            'name' => 'Toddlers',
            'year' => '2026/2027',
            'teacher_ids' => [$one->id, $two->id],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $classroom = Classroom::where('name', 'Toddlers')->firstOrFail();
    expect($classroom->teachers()->pluck('users.id')->all())
        ->toEqualCanonicalizing([$one->id, $two->id]);
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
        ->assertInertia(fn ($p) => $p->where('canManage', true)->has('teachers', 2));

    $this->actingAs(User::factory()->teacher()->create())
        ->get(route('portal.home'))
        ->assertInertia(fn ($p) => $p->where('canManage', false)->has('teachers', 0));
});

test('an admin edits a class', function () {
    $classroom = Classroom::factory()->create([
        'name' => 'Mr James',
        'banner' => 'blocks',
    ]);

    $this->actingAs($this->admin)
        ->patch(route('portal.classes.update', $classroom), [
            'name' => 'Mr James (AM)',
            'grade' => 'Grade 2',
            'year' => '2027/2028',
            'banner' => 'xylophone',
        ])
        ->assertRedirect();

    $fresh = $classroom->fresh();

    expect($fresh->name)->toBe('Mr James (AM)')
        ->and($fresh->grade)->toBe('Grade 2')
        ->and($fresh->year)->toBe('2027/2028')
        ->and($fresh->banner)->toBe('xylophone');
});

test('editing rejects a banner outside the library', function () {
    $classroom = Classroom::factory()->create(['banner' => 'blocks']);

    $this->actingAs($this->admin)
        ->patch(route('portal.classes.update', $classroom), [
            'name' => 'Mr James',
            'year' => '2026/2027',
            'banner' => 'not-a-banner',
        ])
        ->assertSessionHasErrors('banner');

    expect($classroom->fresh()->banner)->toBe('blocks');
});

test('a teacher cannot edit or archive their own room', function () {
    $teacher = User::factory()->teacher()->create();
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $this->actingAs($teacher)
        ->patch(route('portal.classes.update', $classroom), [
            'name' => 'Renamed',
            'year' => '2026/2027',
        ])
        ->assertForbidden();

    $this->actingAs($teacher)
        ->delete(route('portal.classes.destroy', $classroom))
        ->assertForbidden();

    expect($classroom->fresh()->is_archived)->toBeFalse();
});

test('home sends the room teachers so the edit form can preselect them', function () {
    $teacher = User::factory()->teacher()->create();
    Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $this->actingAs($this->admin)
        ->get(route('portal.home'))
        ->assertInertia(fn ($p) => $p->where('classes.0.teachers.0.id', $teacher->id));
});

test('an admin can see archived classes separately and restore them', function () {
    $classroom = Classroom::factory()->archived()->create();

    $this->actingAs($this->admin)
        ->get(route('portal.home'))
        ->assertInertia(fn ($p) => $p
            ->where('archivedClasses.0.id', $classroom->id)
            ->count('classes', 0)
            ->etc());

    $this->actingAs($this->admin)
        ->patch(route('portal.classes.restore', $classroom))
        ->assertRedirect();

    expect($classroom->fresh()->is_archived)->toBeFalse();
});

test('a teacher does not receive the archived class list', function () {
    Classroom::factory()->archived()->create();

    $this->actingAs(User::factory()->teacher()->create())
        ->get(route('portal.home'))
        ->assertInertia(fn ($p) => $p->count('archivedClasses', 0)->etc());
});
