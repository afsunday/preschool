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
                    'audience' => $c->audience,
                    'recipients' => $c->recipients_count,
                    'sentAt' => $c->sent_at?->diffForHumans(),
                ]),
        ]);
    }

    public function send(SendNewsletterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $recipients = NewsletterSubscriber::query()
            ->active()
            ->when(
                $data['audience'] === 'selected',
                fn ($q) => $q->whereIn('id', $data['recipients'] ?? []),
            )
            ->get();

        $campaign = NewsletterCampaign::create([
            'subject' => $data['subject'],
            'body' => $data['body'],
            'audience' => $data['audience'],
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

    /**
     * The exact email that was sent, for the archive's preview pane.
     */
    public function preview(NewsletterCampaign $campaign): View
    {
        return view('emails.newsletter', [
            'campaign' => $campaign,
            'unsubscribeUrl' => '#',
        ]);
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
