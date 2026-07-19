<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Post;
use App\Models\PostComment;
use App\Support\Upload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The class feed. Only staff broadcast; it reaches every guardian of every child
 * in the room (see Classroom::guardians()).
 */
class PortalPostController extends Controller
{
    public function store(Request $request, Classroom $classroom): RedirectResponse
    {
        $this->authorize('staff', $classroom);

        $isEvent = $request->input('type') === Post::TYPE_EVENT;

        $rules = [
            'type' => ['sometimes', Rule::in([Post::TYPE_UPDATE, Post::TYPE_EVENT])],
            // An event's description is optional; a plain update needs a body.
            'body' => [$isEvent ? 'nullable' : 'required', 'string', 'max:5000'],
            // Temp paths from PortalUploadController — the files are already on
            // disk; this only moves them somewhere permanent.
            'photos' => ['array', 'max:10'],
            'photos.*' => ['string'],
        ];

        if ($isEvent) {
            $rules['event_title'] = ['required', 'string', 'max:200'];
            $rules['event_at'] = ['required', 'date'];
            $rules['event_ends_at'] = ['nullable', 'date', 'after:event_at'];
            $rules['event_location'] = ['nullable', 'string', 'max:200'];
        }

        $data = $request->validate($rules);

        $classroom->posts()->create([
            'user_id' => $request->user()->id,
            'type' => $isEvent ? Post::TYPE_EVENT : Post::TYPE_UPDATE,
            'body' => $data['body'] ?? '',
            'event_title' => $isEvent ? $data['event_title'] : null,
            'event_at' => $isEvent ? $data['event_at'] : null,
            'event_ends_at' => $isEvent ? ($data['event_ends_at'] ?? null) : null,
            'event_location' => $isEvent ? ($data['event_location'] ?? null) : null,
            'photos' => Upload::keepAll($data['photos'] ?? [], "posts/{$classroom->id}"),
        ]);

        return back();
    }

    public function destroy(Request $request, Classroom $classroom, Post $post): RedirectResponse
    {
        $this->authorize('staff', $classroom);
        abort_unless($post->classroom_id === $classroom->id, 404);

        // Nothing else can reference these, so they go with the row.
        Upload::removeAll($post->photos);
        $post->delete();

        return back();
    }

    /**
     * Like or unlike a post. Anyone who can see the room's feed may react.
     */
    public function toggleLike(Request $request, Classroom $classroom, Post $post): RedirectResponse
    {
        $this->authorize('view', $classroom);
        abort_unless($post->classroom_id === $classroom->id, 404);

        $post->likers()->toggle($request->user()->id);

        return back();
    }

    public function comment(Request $request, Classroom $classroom, Post $post): RedirectResponse
    {
        $this->authorize('view', $classroom);
        abort_unless($post->classroom_id === $classroom->id, 404);

        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return back();
    }

    public function destroyComment(Request $request, Classroom $classroom, Post $post, PostComment $comment): RedirectResponse
    {
        $this->authorize('view', $classroom);
        abort_unless($post->classroom_id === $classroom->id && $comment->post_id === $post->id, 404);

        // Your own comment, or any of them if you run the room.
        abort_unless(
            $comment->user_id === $request->user()->id || $request->user()->can('staff', $classroom),
            403,
        );

        $comment->delete();

        return back();
    }
}
