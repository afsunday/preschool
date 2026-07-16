<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The class feed. Only staff broadcast; it reaches every guardian of every child
 * in the room (see Classroom::guardians()).
 */
class PortalPostController extends Controller
{
    public function store(Request $request, Classroom $classroom): RedirectResponse
    {
        $this->authorize('staff', $classroom);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'photos' => ['array', 'max:10'],
            'photos.*' => ['integer', 'exists:media,id'],
        ]);

        $post = $classroom->posts()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        foreach ($data['photos'] ?? [] as $i => $mediaId) {
            $post->attachMedia((int) $mediaId, 'photos', $i);
        }

        return back();
    }

    public function destroy(Request $request, Classroom $classroom, Post $post): RedirectResponse
    {
        $this->authorize('staff', $classroom);
        abort_unless($post->classroom_id === $classroom->id, 404);

        $post->delete();

        return back();
    }
}
