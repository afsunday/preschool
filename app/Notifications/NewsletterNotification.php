<?php

namespace App\Notifications;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * One newsletter, delivered to one subscriber. Queued, so sending to a large
 * list doesn't block the request; the Blade view (emails.newsletter) is the
 * design.
 */
class NewsletterNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public NewsletterCampaign $campaign) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(NewsletterSubscriber $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->campaign->subject)
            ->view('emails.newsletter', [
                'campaign' => $this->campaign,
                'unsubscribeUrl' => URL::signedRoute(
                    'newsletter.unsubscribe',
                    ['subscriber' => $notifiable->getKey()],
                ),
            ]);
    }
}
