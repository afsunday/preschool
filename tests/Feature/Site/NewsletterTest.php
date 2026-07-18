<?php

use App\Models\NewsletterSubscriber;

test('a visitor can subscribe to the newsletter', function () {
    $this->from(route('home'))
        ->post(route('newsletter.subscribe'), ['email' => 'ada@example.com'])
        ->assertRedirect(route('home'))
        ->assertSessionHas('newsletterSuccess');

    expect(NewsletterSubscriber::pluck('email')->all())->toBe(['ada@example.com']);
});

test('subscribing twice does not error or duplicate', function () {
    NewsletterSubscriber::factory()->create(['email' => 'ada@example.com']);

    $this->post(route('newsletter.subscribe'), ['email' => 'ada@example.com'])
        ->assertSessionHasNoErrors();

    expect(NewsletterSubscriber::where('email', 'ada@example.com')->count())->toBe(1);
});

test('a valid email is required', function () {
    $this->from(route('home'))
        ->post(route('newsletter.subscribe'), ['email' => 'nope'])
        ->assertRedirect(route('home'))
        ->assertSessionHasErrors('email', errorBag: 'newsletter');

    expect(NewsletterSubscriber::count())->toBe(0);
});

test('an AJAX subscribe returns a JSON message and creates the subscriber', function () {
    $this->postJson(route('newsletter.subscribe'), ['email' => 'ada@example.com'])
        ->assertOk()
        ->assertJsonStructure(['message']);

    expect(NewsletterSubscriber::pluck('email')->all())->toBe(['ada@example.com']);
});

test('an AJAX subscribe with an invalid email returns 422 JSON', function () {
    $this->postJson(route('newsletter.subscribe'), ['email' => 'nope'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('email');

    expect(NewsletterSubscriber::count())->toBe(0);
});
