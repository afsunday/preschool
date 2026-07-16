<?php

use App\Models\Child;
use App\Models\Classroom;
use App\Models\DailyReport;
use App\Models\Post;
use App\Models\User;
use App\Support\Upload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Ordinary uploads: a photo lands in `temp/` the moment it's chosen, then moves
 * somewhere permanent when the form is submitted.
 *
 * These never touch the media library — that is for the public site's CMS.
 */
beforeEach(function () {
    Storage::fake(Upload::DISK);

    $this->teacher = User::factory()->teacher()->create();
    $this->classroom = Classroom::factory()->create(['teacher_id' => $this->teacher->id]);
    $this->child = Child::factory()->create(['classroom_id' => $this->classroom->id]);

    $this->parent = User::factory()->parent()->create();
    $this->child->guardians()->attach($this->parent->id, ['relationship' => 'mum']);
});

function tempPhoto(string $name = 'painting.jpg'): UploadedFile
{
    return UploadedFile::fake()->image($name, 400, 300);
}

// ---- the endpoint ----------------------------------------------------------

test('choosing a photo uploads it straight to temp', function () {
    $response = $this->actingAs($this->teacher)
        ->postJson(route('portal.uploads.store'), ['file' => tempPhoto()])
        ->assertOk()
        ->assertJsonStructure(['path', 'url', 'name', 'size']);

    $path = $response->json('path');

    expect($path)->toStartWith('temp/')
        ->and($response->json('name'))->toBe('painting.jpg');

    Storage::disk(Upload::DISK)->assertExists($path);
});

test('the stored name gives nothing away about the original', function () {
    $path = $this->actingAs($this->teacher)
        ->postJson(route('portal.uploads.store'), ['file' => tempPhoto('tunde-birthday.jpg')])
        ->json('path');

    expect($path)->not->toContain('tunde-birthday');
});

test('a script is refused', function () {
    $this->actingAs($this->teacher)
        ->postJson(route('portal.uploads.store'), [
            'file' => UploadedFile::fake()->create('nasty.php', 8, 'application/x-php'),
        ])
        ->assertStatus(422);
});

test('a guest cannot upload', function () {
    $this->postJson(route('portal.uploads.store'), ['file' => tempPhoto()])
        ->assertUnauthorized();
});

// ---- promotion on submit ---------------------------------------------------

test('posting moves the photo out of temp and keeps it', function () {
    $path = $this->actingAs($this->teacher)
        ->postJson(route('portal.uploads.store'), ['file' => tempPhoto()])
        ->json('path');

    $this->actingAs($this->teacher)
        ->post(route('portal.classes.posts.store', $this->classroom), [
            'body' => 'Painting day!',
            'photos' => [$path],
        ])
        ->assertRedirect();

    $post = Post::first();

    expect($post->photos)->toHaveCount(1)
        ->and($post->photos[0])->toStartWith("posts/{$this->classroom->id}/")
        ->and($post->photoUrls()[0])->toContain($post->photos[0]);

    Storage::disk(Upload::DISK)->assertExists($post->photos[0]);
    Storage::disk(Upload::DISK)->assertMissing($path);
});

test('a path outside temp cannot be promoted', function () {
    // Otherwise a crafted path could move any file on the disk.
    Storage::disk(Upload::DISK)->put('posts/999/someone-elses.jpg', 'bytes');

    $this->actingAs($this->teacher)
        ->post(route('portal.classes.posts.store', $this->classroom), [
            'body' => 'Nice try',
            'photos' => ['posts/999/someone-elses.jpg'],
        ])
        ->assertStatus(422);

    Storage::disk(Upload::DISK)->assertExists('posts/999/someone-elses.jpg');
});

test('an expired temp path is rejected rather than silently dropped', function () {
    $this->actingAs($this->teacher)
        ->post(route('portal.classes.posts.store', $this->classroom), [
            'body' => 'Hello',
            'photos' => ['temp/never-existed.jpg'],
        ])
        ->assertStatus(422);

    expect(Post::count())->toBe(0);
});

test('a day-log entry keeps its photos too', function () {
    $path = $this->actingAs($this->teacher)
        ->postJson(route('portal.uploads.store'), ['file' => tempPhoto()])
        ->json('path');

    $this->actingAs($this->teacher)
        ->post(route('portal.report.entries.store', $this->child), [
            'type' => 'note',
            'note' => 'First steps!',
            'photos' => [$path],
        ]);

    $entry = DailyReport::first()->entries->first();

    expect($entry->photos[0])->toStartWith("reports/{$this->child->id}/");
    Storage::disk(Upload::DISK)->assertExists($entry->photos[0]);
});

test('a parent can attach a photo to a chat message', function () {
    $thread = $this->classroom->conversations()->create(['guardian_id' => $this->parent->id]);

    $path = $this->actingAs($this->parent)
        ->postJson(route('portal.uploads.store'), ['file' => tempPhoto()])
        ->json('path');

    $this->actingAs($this->parent)
        ->post(route('portal.classes.messages.store', [$this->classroom, $thread]), [
            'body' => 'Look what she made',
            'photos' => [$path],
        ])
        ->assertRedirect();

    expect($thread->messages()->first()->photos[0])
        ->toStartWith("chats/{$this->classroom->id}/");
});

test('deleting a post takes its photos with it', function () {
    $path = $this->actingAs($this->teacher)
        ->postJson(route('portal.uploads.store'), ['file' => tempPhoto()])
        ->json('path');

    $this->actingAs($this->teacher)->post(route('portal.classes.posts.store', $this->classroom), [
        'body' => 'Painting day!',
        'photos' => [$path],
    ]);

    $post = Post::first();
    $kept = $post->photos[0];

    $this->actingAs($this->teacher)
        ->delete(route('portal.classes.posts.destroy', [$this->classroom, $post]));

    Storage::disk(Upload::DISK)->assertMissing($kept);
});

test('promoting the same path twice is a no-op, not a failure', function () {
    // An edit re-submitting an already-stored photo must not blow up.
    Storage::disk(Upload::DISK)->put('posts/5/keep.jpg', 'bytes');

    expect(Upload::keep('posts/5/keep.jpg', 'posts/5'))->toBe('posts/5/keep.jpg');
    Storage::disk(Upload::DISK)->assertExists('posts/5/keep.jpg');
});
