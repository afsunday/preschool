<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendNewsletterRequest;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Notifications\NewsletterNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class NewsletterController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('newsletter/index', [
            'subscribers' => NewsletterSubscriber::query()->latest()->get()
                ->map(fn (NewsletterSubscriber $s) => [
                    'id' => $s->id,
                    'email' => $s->email,
                    'active' => $s->unsubscribed_at === null,
                    'subscribedAt' => $s->created_at?->diffForHumans(),
                ]),
            'activeCount' => NewsletterSubscriber::query()->active()->count(),
            'campaigns' => NewsletterCampaign::query()->latest()->get()
                ->map(fn (NewsletterCampaign $c) => [
                    'id' => $c->id,
                    'subject' => $c->subject,
                    'recipients' => $c->recipients_count,
                    'sentAt' => $c->sent_at?->diffForHumans(),
                ]),
        ]);
    }

    public function send(SendNewsletterRequest $request): RedirectResponse
    {
        $recipients = NewsletterSubscriber::query()->active()->get();

        $campaign = NewsletterCampaign::create([
            'subject' => $request->validated()['subject'],
            'body' => $request->validated()['body'],
            'recipients_count' => $recipients->count(),
            'sent_at' => now(),
        ]);

        // Queued (the notification is ShouldQueue): one job per subscriber, so a
        // large list doesn't hold up the request.
        Notification::send($recipients, new NewsletterNotification($campaign));

        return back()->with('success', __(':count subscriber(s) queued.', [
            'count' => $recipients->count(),
        ]));
    }

    public function destroySubscriber(NewsletterSubscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return back()->with('success', __('Subscriber removed.'));
    }

    /**
     * Public one-click unsubscribe from the email footer (signed URL).
     */
    public function unsubscribe(NewsletterSubscriber $subscriber): View
    {
        $subscriber->update(['unsubscribed_at' => now()]);

        return view('newsletter.unsubscribed');
    }
}
