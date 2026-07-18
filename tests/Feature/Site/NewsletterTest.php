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
