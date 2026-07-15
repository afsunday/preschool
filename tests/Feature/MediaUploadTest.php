<?php

use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->create(['user_type' => 'admin']);
});

test('an admin can upload an image and a document', function () {
    $this->actingAs($this->admin)
        ->postJson(route('media.items.store'), [
            'files' => [
                UploadedFile::fake()->image('hero.jpg', 800, 600),
                UploadedFile::fake()->create('policy.pdf', 120, 'application/pdf'),
            ],
        ])
        ->assertCreated()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.kind', 'image')
        ->assertJsonPath('data.0.width', 800)
        ->assertJsonPath('data.1.kind', 'document');

    expect(Media::count())->toBe(2);
    Storage::disk('public')->assertExists(Media::first()->path);
});

test('the index lists, searches and filters by kind', function () {
    Media::factory()->create(['title' => 'Sunny classroom', 'kind' => 'image']);
    Media::factory()->create(['title' => 'Enrolment form', 'kind' => 'document']);

    $this->actingAs($this->admin);

    $this->getJson(route('media.items.index'))->assertOk()->assertJsonCount(2, 'data');
    $this->getJson(route('media.items.index', ['q' => 'sunny']))->assertJsonCount(1, 'data');
    $this->getJson(route('media.items.index', ['kind' => 'document']))
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Enrolment form');
});

test('non-admins cannot upload', function () {
    $this->actingAs(User::factory()->create(['user_type' => 'user']))
        ->postJson(route('media.items.store'), [
            'files' => [UploadedFile::fake()->image('x.jpg')],
        ])
        ->assertForbidden();
});

test('an admin can edit media metadata', function () {
    $media = Media::factory()->create(['title' => 'old', 'alt' => null]);

    $this->actingAs($this->admin)
        ->patchJson(route('media.items.update', $media), [
            'title' => 'Sunny classroom',
            'alt' => 'Children painting at a table',
        ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Sunny classroom')
        ->assertJsonPath('data.alt', 'Children painting at a table');

    expect($media->fresh()->title)->toBe('Sunny classroom');
});

test('an unused file is deleted along with its stored file', function () {
    $file = UploadedFile::fake()->image('gone.jpg');
    $created = $this->actingAs($this->admin)
        ->postJson(route('media.items.store'), ['files' => [$file]])
        ->json('data.0');

    $media = Media::find($created['id']);
    Storage::disk('public')->assertExists($media->path);

    $this->actingAs($this->admin)
        ->deleteJson(route('media.items.destroy', $media))
        ->assertNoContent();

    expect(Media::withTrashed()->find($media->id))->toBeNull();
    Storage::disk('public')->assertMissing($media->path);
});

test('a file that is in use cannot be deleted', function () {
    $media = Media::factory()->create();

    // Simulate an attachment via the mediables pivot.
    DB::table('mediables')->insert([
        'media_id' => $media->id,
        'mediable_type' => 'App\\Models\\Page',
        'mediable_id' => 7,
        'collection' => 'hero',
        'position' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($this->admin)
        ->deleteJson(route('media.items.destroy', $media))
        ->assertStatus(409)
        ->assertJsonPath('usages.0.type', 'Page');

    expect(Media::find($media->id))->not->toBeNull();
});
