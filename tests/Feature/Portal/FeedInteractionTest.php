<?php

use App\Models\Child;
use App\Models\Classroom;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\User;

beforeEach(function () {
    $this->teacher = User::factory()->teacher()->create();
    $this->classroom = Classroom::factory()->create(['teacher_id' => $this->teacher->id]);

    $this->parent = User::factory()->parent()->create();
    Child::factory()->create(['classroom_id' => $this->classroom->id])
        ->guardians()->attach($this->parent->id, ['relationship' => 'mum']);

    $this->post = Post::factory()->create([
        'classroom_id' => $this->classroom->id,
        'user_id' => $this->teacher->id,
    ]);
});

test('a parent can like and unlike a post', function () {
    $this->actingAs($this->parent)
        ->post(route('portal.classes.posts.like', [$this->classroom, $this->post]))
        ->assertRedirect();
    expect($this->post->likers()->count())->toBe(1);

    $this->actingAs($this->parent)
        ->post(route('portal.classes.posts.like', [$this->classroom, $this->post]))
        ->assertRedirect();
    expect($this->post->likers()->count())->toBe(0);
});

test('a parent can comment on a post', function () {
    $this->actingAs($this->parent)
        ->post(route('portal.classes.posts.comments.store', [$this->classroom, $this->post]), ['body' => 'So sweet!'])
        ->assertRedirect();

    expect($this->post->comments()->count())->toBe(1)
        ->and($this->post->comments()->first()->body)->toBe('So sweet!');
});

test('a stranger can neither like nor comment', function () {
    $stranger = User::factory()->parent()->create();

    $this->actingAs($stranger)
        ->post(route('portal.classes.posts.like', [$this->classroom, $this->post]))
        ->assertForbidden();

    $this->actingAs($stranger)
        ->post(route('portal.classes.posts.comments.store', [$this->classroom, $this->post]), ['body' => 'hi'])
        ->assertForbidden();
});

test('a parent can delete their own comment but not another family\'s', function () {
    $comment = $this->post->comments()->create(['user_id' => $this->parent->id, 'body' => 'mine']);

    $other = User::factory()->parent()->create();
    Child::factory()->create(['classroom_id' => $this->classroom->id])
        ->guardians()->attach($other->id, ['relationship' => 'dad']);

    $this->actingAs($other)
        ->delete(route('portal.classes.posts.comments.destroy', [$this->classroom, $this->post, $comment]))
        ->assertForbidden();

    $this->actingAs($this->parent)
        ->delete(route('portal.classes.posts.comments.destroy', [$this->classroom, $this->post, $comment]))
        ->assertRedirect();

    expect(PostComment::find($comment->id))->toBeNull();
});

test('staff can delete any comment', function () {
    $comment = $this->post->comments()->create(['user_id' => $this->parent->id, 'body' => 'mine']);

    $this->actingAs($this->teacher)
        ->delete(route('portal.classes.posts.comments.destroy', [$this->classroom, $this->post, $comment]))
        ->assertRedirect();

    expect(PostComment::find($comment->id))->toBeNull();
});

test('a teacher can post an event', function () {
    $this->actingAs($this->teacher)
        ->post(route('portal.classes.posts.store', $this->classroom), [
            'type' => 'event',
            'event_title' => 'Swimming lesson',
            'event_at' => '2026-07-24 09:30',
            'event_ends_at' => '2026-07-24 11:00',
            'event_location' => 'Community Pool',
            'body' => 'Pack a towel.',
        ])
        ->assertRedirect();

    $event = Post::where('type', 'event')->first();

    expect($event)->not->toBeNull()
        ->and($event->event_title)->toBe('Swimming lesson')
        ->and($event->event_location)->toBe('Community Pool')
        ->and($event->event_at->format('H:i'))->toBe('09:30');
});

test('an event requires a title and a date', function () {
    $this->actingAs($this->teacher)
        ->post(route('portal.classes.posts.store', $this->classroom), ['type' => 'event'])
        ->assertSessionHasErrors(['event_title', 'event_at']);
});

test('the feed serializes an event card', function () {
    $this->classroom->posts()->create([
        'user_id' => $this->teacher->id,
        'type' => 'event',
        'body' => '',
        'event_title' => 'Picture Day',
        'event_at' => now()->setTime(9, 0),
    ]);

    $this->actingAs($this->parent)
        ->get(route('portal.classes.feed', $this->classroom))
        ->assertOk()
        ->assertInertia(fn ($p) => $p
            ->where('posts.0.type', 'event')
            ->where('posts.0.event.title', 'Picture Day'));
});

test('the feed serializes likes and comments for the viewer', function () {
    $this->post->likers()->attach($this->parent->id);
    $this->post->comments()->create(['user_id' => $this->teacher->id, 'body' => 'Hello families']);

    $this->actingAs($this->parent)
        ->get(route('portal.classes.feed', $this->classroom))
        ->assertOk()
        ->assertInertia(fn ($p) => $p
            ->where('posts.0.likesCount', 1)
            ->where('posts.0.likedByMe', true)
            ->where('posts.0.comments.0.body', 'Hello families'));
});
