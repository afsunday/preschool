<?php

namespace App\Http\Controllers;

use App\Models\ContactSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ContactSubmissionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('messages/index', [
            'submissions' => ContactSubmission::query()->latest()->get()
                ->map(fn (ContactSubmission $s) => [
                    'id' => $s->id,
                    'name' => Str::of("{$s->first_name} {$s->last_name}")->trim()->toString() ?: null,
                    'email' => $s->email,
                    'message' => $s->message,
                    'isRead' => $s->is_read,
                    'receivedAt' => $s->created_at?->diffForHumans(),
                ]),
            'unread' => ContactSubmission::query()->where('is_read', false)->count(),
        ]);
    }

    public function update(ContactSubmission $submission): RedirectResponse
    {
        $submission->update(['is_read' => ! $submission->is_read]);

        return back();
    }

    public function destroy(ContactSubmission $submission): RedirectResponse
    {
        $submission->delete();

        return back()->with('success', __('Message deleted.'));
    }
}
