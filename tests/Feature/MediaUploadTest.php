<?php

use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
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
