<?php

namespace App\Http\Controllers;

use App\Http\Controllers\SitePageController as Site;
use App\Models\ContactSubmission;
use App\Models\Material;
use App\Models\Media;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        // The dashboard is the back office. Teachers and parents belong in the
        // portal — bounce them there rather than showing admin figures.
        if (! $request->user()->isAdmin()) {
            return redirect('/portal');
        }

        return Inertia::render('dashboard', [
            'stats' => [
                'pages' => Page::query()->where('slug', '!=', Site::GLOBALS)->count(),
                'materials' => Material::query()->count(),
                'materialsPublished' => Material::query()->published()->count(),
                'media' => Media::query()->count(),
                'subscribers' => NewsletterSubscriber::query()->active()->count(),
                'messagesUnread' => ContactSubmission::query()->where('is_read', false)->count(),
                'messagesTotal' => ContactSubmission::query()->count(),
            ],
            'recentMessages' => ContactSubmission::query()->latest()->limit(5)->get()
                ->map(fn (ContactSubmission $s) => [
                    'id' => $s->id,
                    'name' => Str::of("{$s->first_name} {$s->last_name}")->trim()->toString() ?: null,
                    'email' => $s->email,
                    'isRead' => $s->is_read,
                    'receivedAt' => $s->created_at?->diffForHumans(),
                ]),
        ]);
    }
}
