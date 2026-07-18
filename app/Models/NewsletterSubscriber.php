<?php

namespace App\Models;

use Database\Factories\NewsletterSubscriberFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class NewsletterSubscriber extends Model
{
    /**
     * @use HasFactory<NewsletterSubscriberFactory>
     */
    use HasFactory;

    // routeNotificationForMail() defaults to the `email` attribute, so a
    // subscriber is a valid mail notifiable out of the box.
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['email', 'unsubscribed_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unsubscribed_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<NewsletterSubscriber>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->whereNull('unsubscribed_at');
    }
}
