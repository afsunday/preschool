<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Conversation;
use App\Support\Upload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Teacher ↔ parent chat.
 *
 * The thread belongs to the room, not to a teacher, so any staff on the room may
 * reply — that's what lets co-teachers arrive later for free.
 */
class PortalMessageController extends Controller
{
    public function store(Request $request, Classroom $classroom, Conversation $conversation): RedirectResponse
    {
        $this->authorize('view', $classroom);
        abort_unless($conversation->classroom_id === $classroom->id, 404);

        $user = $request->user();
        $isStaff = $user->can('staff', $classroom);

        // Either you're staff on this room, or it's your own thread.
        abort_unless($isStaff || $conversation->guardian_id === $user->id, 403);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'photos' => ['array', 'max:10'],
            'photos.*' => ['string'],
        ]);

        $message = $conversation->messages()->create([
            'user_id' => $user->id,
            'body' => $data['body'],
            'photos' => Upload::keepAll($data['photos'] ?? [], "chats/{$classroom->id}"),
        ]);

        // Stamp the thread and mark it read for the sender — the other side stays
        // unread, which is the whole of our unread logic.
        $conversation->forceFill([
            'last_message_at' => $message->created_at,
            $isStaff ? 'teacher_read_at' : 'guardian_read_at' => $message->created_at,
        ])->save();

        return back();
    }
}
