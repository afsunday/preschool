<?php

use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['user_type' => 'admin']);
});

test('an admin sees the materials index', function () {
    Material::factory()->create();

    $this->actingAs($this->admin)->get('/admin/materials')->assertOk();
});

test('a non-admin cannot reach the materials admin', function () {
    $user = User::factory()->create(['user_type' => 'user']);

    $this->actingAs($user)->get('/admin/materials')->assertForbidden();
    $this->actingAs($user)->post('/admin/materials', ['title' => 'x', 'type' => 'article'])
        ->assertForbidden();
});

test('an admin can create a published, featured material', function () {
    $category = MaterialCategory::factory()->create();

    $this->actingAs($this->admin)->post('/admin/materials', [
        'title' => 'Kindergarten Readiness Checklist',
        'type' => 'download',
        'category_id' => $category->id,
        'url' => '/files/readiness.pdf',
        'image_path' => '/images/about/program-classroom.jpg',
        'is_featured' => true,
        'is_published' => true,
    ])->assertRedirect();

    $material = Material::sole();

    expect($material->title)->toBe('Kindergarten Readiness Checklist')
        ->and($material->category_id)->toBe($category->id)
        ->and($material->is_featured)->toBeTrue()
        ->and($material->published_at)->not->toBeNull();
});

test('an unpublished material is a draft', function () {
    $this->actingAs($this->admin)->post('/admin/materials', [
        'title' => 'Draft note',
        'type' => 'article',
        'is_published' => false,
    ]);

    expect(Material::sole()->published_at)->toBeNull();
});

test('title and type are required', function () {
    $this->actingAs($this->admin)->post('/admin/materials', [])
        ->assertSessionHasErrors(['title', 'type']);

    expect(Material::count())->toBe(0);
});

test('updating a live material keeps its original publish date', function () {
    $material = Material::factory()->create(['published_at' => now()->subWeek()]);
    $original = $material->published_at;

    $this->actingAs($this->admin)->put("/admin/materials/{$material->id}", [
        'title' => 'Renamed',
        'type' => $material->type,
        'is_published' => true,
    ])->assertRedirect();

    expect($material->fresh()->title)->toBe('Renamed')
        ->and($material->fresh()->published_at->timestamp)->toBe($original->timestamp);
});

test('an admin can delete a material', function () {
    $material = Material::factory()->create();

    $this->actingAs($this->admin)->delete("/admin/materials/{$material->id}")->assertRedirect();

    expect(Material::count())->toBe(0);
});

test('an admin can add, rename and delete a category', function () {
    $this->actingAs($this->admin)
        ->post('/admin/material-categories', ['name' => 'Parenting'])
        ->assertRedirect();

    $category = MaterialCategory::sole();
    expect($category->slug)->toBe('parenting');

    // Renaming keeps the slug stable so links don't break.
    $this->actingAs($this->admin)
        ->put("/admin/material-categories/{$category->id}", ['name' => 'Parenting Tips'])
        ->assertRedirect();

    expect($category->fresh()->name)->toBe('Parenting Tips')
        ->and($category->fresh()->slug)->toBe('parenting');

    $this->actingAs($this->admin)
        ->delete("/admin/material-categories/{$category->id}")
        ->assertRedirect();

    expect(MaterialCategory::count())->toBe(0);
});

test('deleting a category leaves its materials uncategorised', function () {
    $category = MaterialCategory::factory()->create();
    $material = Material::factory()->create(['category_id' => $category->id]);

    $this->actingAs($this->admin)->delete("/admin/material-categories/{$category->id}");

    expect($material->fresh()->category_id)->toBeNull();
});
