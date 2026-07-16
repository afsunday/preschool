<?php

use App\Models\Child;
use App\Models\Classroom;
use App\Models\Conversation;
use App\Models\DailyReport;
use App\Models\User;

beforeEach(function () {
    $this->teacher = User::factory()->teacher()->create();
    $this->classroom = Classroom::factory()->create(['teacher_id' => $this->teacher->id]);

    $this->parent = User::factory()->parent()->create();
    $this->child = Child::factory()->create(['classroom_id' => $this->classroom->id]);
    $this->child->guardians()->attach($this->parent->id, ['relationship' => 'mum']);
});

// ---- feed ------------------------------------------------------------------

test('a teacher posts to the class feed', function () {
    $this->actingAs($this->teacher)
        ->post(route('portal.classes.posts.store', $this->classroom), ['body' => 'Painting day!'])
        ->assertRedirect();

    expect($this->classroom->posts()->count())->toBe(1);
});

test('a parent cannot post to the class feed', function () {
    $this->actingAs($this->parent)
        ->post(route('portal.classes.posts.store', $this->classroom), ['body' => 'Hello'])
        ->assertForbidden();

    expect($this->classroom->posts()->count())->toBe(0);
});

test('a post reaches every guardian of every child in the room', function () {
    $other = User::factory()->parent()->create();
    Child::factory()
        ->create(['classroom_id' => $this->classroom->id])
        ->guardians()->attach($other->id, ['relationship' => 'dad']);

    expect($this->classroom->guardians()->pluck('id'))
        ->toContain($this->parent->id, $other->id)
        ->toHaveCount(2);
});

// ---- chat ------------------------------------------------------------------

test('a parent opening chat lands in their own thread, created on demand', function () {
    $this->actingAs($this->parent)
        ->get(route('portal.classes.chats', $this->classroom))
        ->assertOk()
        ->assertInertia(fn ($p) => $p->has('active')->has('threads', 1));

    expect(Conversation::where('guardian_id', $this->parent->id)->count())->toBe(1);
});

test('sending a message stamps the thread and leaves the other side unread', function () {
    $thread = Conversation::factory()->create([
        'classroom_id' => $this->classroom->id,
        'guardian_id' => $this->parent->id,
    ]);

    $this->actingAs($this->parent)
        ->post(route('portal.classes.messages.store', [$this->classroom, $thread]), ['body' => 'Hello'])
        ->assertRedirect();

    $thread->refresh();

    expect($thread->last_message_at)->not->toBeNull()
        ->and($thread->isUnreadFor($this->teacher))->toBeTrue()
        ->and($thread->isUnreadFor($this->parent))->toBeFalse();
});

test('a parent cannot read another parent\'s thread', function () {
    $stranger = User::factory()->parent()->create();
    Child::factory()
        ->create(['classroom_id' => $this->classroom->id])
        ->guardians()->attach($stranger->id, ['relationship' => 'mum']);

    $theirs = Conversation::factory()->create([
        'classroom_id' => $this->classroom->id,
        'guardian_id' => $stranger->id,
    ]);

    $this->actingAs($this->parent)
        ->get(route('portal.classes.chats', [$this->classroom, $theirs]))
        ->assertForbidden();
});

test('staff see every thread in the room', function () {
    Conversation::factory()->count(2)->create(['classroom_id' => $this->classroom->id]);

    $this->actingAs($this->teacher)
        ->get(route('portal.classes.chats', $this->classroom))
        ->assertOk()
        ->assertInertia(fn ($p) => $p->has('threads', 2));
});

// ---- daily report ----------------------------------------------------------

test('logging an entry creates the day\'s report on demand', function () {
    $this->actingAs($this->teacher)
        ->post(route('portal.report.entries.store', $this->child), ['type' => 'nap'])
        ->assertRedirect();

    $report = DailyReport::where('child_id', $this->child->id)->first();

    expect($report)->not->toBeNull()
        ->and($report->entries)->toHaveCount(1)
        ->and($report->isPublished())->toBeFalse();
});

test('a draft report is hidden from the parent until it is published', function () {
    $report = DailyReport::factory()->create([
        'child_id' => $this->child->id,
        'date' => today(),
    ]);

    $this->actingAs($this->parent)
        ->get(route('portal.classes.today', $this->classroom))
        ->assertOk()
        ->assertInertia(fn ($p) => $p->where('children.0.report', null));

    $report->publish();

    $this->actingAs($this->parent)
        ->get(route('portal.classes.today', $this->classroom))
        ->assertOk()
        ->assertInertia(fn ($p) => $p->where('children.0.report.published', true));
});

test('staff see their own drafts', function () {
    DailyReport::factory()->create(['child_id' => $this->child->id, 'date' => today()]);

    $this->actingAs($this->teacher)
        ->get(route('portal.classes.today', $this->classroom))
        ->assertOk()
        ->assertInertia(fn ($p) => $p->where('children.0.report.published', false));
});

test('a parent cannot write a report', function () {
    $this->actingAs($this->parent)
        ->post(route('portal.report.entries.store', $this->child), ['type' => 'nap'])
        ->assertForbidden();
});

test('a parent only sees their own child on the Today screen', function () {
    Child::factory()->count(3)->create(['classroom_id' => $this->classroom->id]);

    $this->actingAs($this->parent)
        ->get(route('portal.classes.today', $this->classroom))
        ->assertOk()
        ->assertInertia(fn ($p) => $p->has('children', 1)
            ->where('children.0.id', $this->child->id));

    // …while staff see the whole room.
    $this->actingAs($this->teacher)
        ->get(route('portal.classes.today', $this->classroom))
        ->assertInertia(fn ($p) => $p->has('children', 4));
});

test('an entry type outside the allowed set is rejected', function () {
    $this->actingAs($this->teacher)
        ->post(route('portal.report.entries.store', $this->child), ['type' => 'karate'])
        ->assertSessionHasErrors('type');
});
