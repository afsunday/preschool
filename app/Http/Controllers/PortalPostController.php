<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Post;
use App\Support\Upload;
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
            // Temp paths from PortalUploadController — the files are already on
            // disk; this only moves them somewhere permanent.
            'photos' => ['array', 'max:10'],
            'photos.*' => ['string'],
        ]);

        $classroom->posts()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
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
}
