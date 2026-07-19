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
        ->assertInertia(fn ($p) => $p->has('active'));

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

test('staff see every family in the room, even without a thread', function () {
    // beforeEach already added one family (the parent) with no thread yet.
    $other = User::factory()->parent()->create();
    Child::factory()->create(['classroom_id' => $this->classroom->id])
        ->guardians()->attach($other->id, ['relationship' => 'dad']);

    $this->actingAs($this->teacher)
        ->get(route('portal.classes.chats', $this->classroom))
        ->assertOk()
        ->assertInertia(fn ($p) => $p->has('families', 2));
});

test('a teacher can start a conversation with a family that has not messaged', function () {
    expect(Conversation::where('guardian_id', $this->parent->id)->count())->toBe(0);

    $this->actingAs($this->teacher)
        ->get(route('portal.classes.chats', ['classroom' => $this->classroom, 'guardian' => $this->parent->id]))
        ->assertOk()
        ->assertInertia(fn ($p) => $p->has('active'));

    expect(Conversation::where([
        'classroom_id' => $this->classroom->id,
        'guardian_id' => $this->parent->id,
    ])->count())->toBe(1);
});

test('a teacher cannot start a chat with someone who is not a family in the room', function () {
    $outsider = User::factory()->parent()->create();

    $this->actingAs($this->teacher)
        ->get(route('portal.classes.chats', ['classroom' => $this->classroom, 'guardian' => $outsider->id]))
        ->assertNotFound();
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

// ---- day sheet: real inputs, not hardcoded ---------------------------------

test('a meal records which meal it was and how it went', function () {
    $this->actingAs($this->teacher)
        ->post(route('portal.report.entries.store', $this->child), [
            'type' => 'meal',
            'label' => 'Lunch',
            'detail' => 'Ate some',
            'occurred_at' => today()->setTime(12, 15)->toDateTimeString(),
        ])
        ->assertRedirect();

    $entry = DailyReport::where('child_id', $this->child->id)->first()->entries->first();

    expect($entry->label)->toBe('Lunch')
        ->and($entry->detail)->toBe('Ate some')
        ->and($entry->occurred_at->format('H:i'))->toBe('12:15');
});

test('a meal detail outside the offered set is rejected', function () {
    // The old quick-log hardcoded "Ate all"; now the value must be a real one.
    $this->actingAs($this->teacher)
        ->post(route('portal.report.entries.store', $this->child), [
            'type' => 'meal',
            'detail' => 'Ate a horse',
        ])
        ->assertSessionHasErrors('detail');
});

test('a meal must say how it went', function () {
    $this->actingAs($this->teacher)
        ->post(route('portal.report.entries.store', $this->child), ['type' => 'meal'])
        ->assertSessionHasErrors('detail');
});

test('a note takes free text and needs no detail', function () {
    $this->actingAs($this->teacher)
        ->post(route('portal.report.entries.store', $this->child), [
            'type' => 'note',
            'note' => 'Wobbly tooth came out.',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();
});

test('a nap cannot end before it starts', function () {
    $this->actingAs($this->teacher)
        ->post(route('portal.report.entries.store', $this->child), [
            'type' => 'nap',
            'occurred_at' => today()->setTime(14, 0)->toDateTimeString(),
            'ended_at' => today()->setTime(12, 0)->toDateTimeString(),
        ])
        ->assertSessionHasErrors('ended_at');
});

test('an entry can be removed', function () {
    $this->actingAs($this->teacher)
        ->post(route('portal.report.entries.store', $this->child), ['type' => 'nap']);

    $report = DailyReport::where('child_id', $this->child->id)->first();
    $entry = $report->entries->first();

    $this->actingAs($this->teacher)
        ->delete(route('portal.report.entries.destroy', [$this->child, $entry]))
        ->assertRedirect();

    expect($report->fresh()->entries)->toHaveCount(0);
});

test('publishing is a gate, not a send — later entries are visible at once', function () {
    // The teacher opens the day, then logs more. The parent sees the additions
    // without anything being "sent" again.
    $this->actingAs($this->teacher)
        ->post(route('portal.report.entries.store', $this->child), ['type' => 'nap']);
    $this->actingAs($this->teacher)
        ->post(route('portal.report.publish', $this->child));
    $this->actingAs($this->teacher)
        ->post(route('portal.report.entries.store', $this->child), [
            'type' => 'meal', 'label' => 'Lunch', 'detail' => 'Ate all',
        ]);

    $this->actingAs($this->parent)
        ->get(route('portal.classes.today', $this->classroom))
        ->assertInertia(fn ($p) => $p
            ->where('children.0.report.published', true)
            ->has('children.0.report.entries', 2));
});

test('a day can be hidden again after being sent by mistake', function () {
    $this->actingAs($this->teacher)
        ->post(route('portal.report.entries.store', $this->child), ['type' => 'nap']);
    $this->actingAs($this->teacher)
        ->post(route('portal.report.publish', $this->child));

    $this->actingAs($this->teacher)
        ->delete(route('portal.report.unpublish', $this->child))
        ->assertRedirect();

    expect(DailyReport::where('child_id', $this->child->id)->first()->isPublished())->toBeFalse();

    // …and the parent loses sight of it again.
    $this->actingAs($this->parent)
        ->get(route('portal.classes.today', $this->classroom))
        ->assertInertia(fn ($p) => $p->where('children.0.report', null));
});

test('a parent cannot hide their own report', function () {
    $this->actingAs($this->parent)
        ->delete(route('portal.report.unpublish', $this->child))
        ->assertForbidden();
});

test('mood is logged as an entry with its own time, not once for the day', function () {
    // A child can be happy at 9am and upset by 3pm — mood belongs on the timeline.
    $this->actingAs($this->teacher)
        ->post(route('portal.report.entries.store', $this->child), [
            'type' => 'mood',
            'detail' => 'Happy',
            'occurred_at' => today()->setTime(9, 15)->toDateTimeString(),
        ])
        ->assertRedirect();

    $this->actingAs($this->teacher)
        ->post(route('portal.report.entries.store', $this->child), [
            'type' => 'mood',
            'detail' => 'Upset',
            'occurred_at' => today()->setTime(15, 0)->toDateTimeString(),
        ])
        ->assertRedirect();

    $entries = DailyReport::where('child_id', $this->child->id)->first()->entries;

    expect($entries)->toHaveCount(2)
        ->and($entries->pluck('detail')->all())->toBe(['Happy', 'Upset'])
        ->and($entries->first()->occurred_at->format('H:i'))->toBe('09:15');
});

test('a mood outside the offered set is rejected', function () {
    $this->actingAs($this->teacher)
        ->post(route('portal.report.entries.store', $this->child), [
            'type' => 'mood',
            'detail' => 'Feral',
        ])
        ->assertSessionHasErrors('detail');
});
