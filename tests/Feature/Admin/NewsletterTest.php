<?php

use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use App\Notifications\NewsletterNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    $this->admin = User::factory()->create(['user_type' => 'admin']);
});

test('an admin sees the newsletter screen', function () {
    $this->actingAs($this->admin)->get('/admin/newsletter')->assertOk();
});

test('a non-admin cannot reach the newsletter admin', function () {
    $user = User::factory()->create(['user_type' => 'user']);

    $this->actingAs($user)->get('/admin/newsletter')->assertForbidden();
    $this->actingAs($user)
        ->post('/admin/newsletter/send', ['subject' => 'x', 'body' => 'y'])
        ->assertForbidden();
});

test('sending queues the email to active subscribers only', function () {
    Notification::fake();

    $active = NewsletterSubscriber::factory()->count(3)->create();
    $unsub = NewsletterSubscriber::factory()->create(['unsubscribed_at' => now()]);

    $this->actingAs($this->admin)
        ->post('/admin/newsletter/send', [
            'subject' => 'Autumn term news',
            'body' => "Hello!\n\nHere's what's happening.",
        ])
        ->assertRedirect();

    Notification::assertSentTo($active, NewsletterNotification::class);
    Notification::assertNotSentTo($unsub, NewsletterNotification::class);
});

test('sending records a campaign with the recipient count', function () {
    Notification::fake();
    NewsletterSubscriber::factory()->count(2)->create();
    NewsletterSubscriber::factory()->create(['unsubscribed_at' => now()]);

    $this->actingAs($this->admin)->post('/admin/newsletter/send', [
        'subject' => 'Hello',
        'body' => 'World',
    ]);

    $campaign = NewsletterCampaign::sole();

    expect($campaign->subject)->toBe('Hello')
        ->and($campaign->recipients_count)->toBe(2)
        ->and($campaign->sent_at)->not->toBeNull();
});

test('the newsletter notification is queued', function () {
    expect(new NewsletterNotification(new NewsletterCampaign))
        ->toBeInstanceOf(ShouldQueue::class);
});

test('subject and body are required to send', function () {
    Notification::fake();

    $this->actingAs($this->admin)->post('/admin/newsletter/send', [])
        ->assertSessionHasErrors(['subject', 'body']);

    expect(NewsletterCampaign::count())->toBe(0);
    Notification::assertNothingSent();
});

test('an admin can remove a subscriber', function () {
    $subscriber = NewsletterSubscriber::factory()->create();

    $this->actingAs($this->admin)
        ->delete("/admin/newsletter/subscribers/{$subscriber->id}")
        ->assertRedirect();

    expect(NewsletterSubscriber::count())->toBe(0);
});

test('the signed unsubscribe link marks the subscriber inactive', function () {
    $subscriber = NewsletterSubscriber::factory()->create(['unsubscribed_at' => null]);

    $url = URL::signedRoute('newsletter.unsubscribe', ['subscriber' => $subscriber->id]);

    $this->get($url)->assertOk()->assertSee('unsubscribed');

    expect($subscriber->fresh()->unsubscribed_at)->not->toBeNull();
});

test('an unsigned unsubscribe link is rejected', function () {
    $subscriber = NewsletterSubscriber::factory()->create();

    $this->get("/newsletter/unsubscribe/{$subscriber->id}")->assertForbidden();
});
