<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

/**
 * Creating and editing classes — admin only (a teacher runs a room, they don't
 * open one).
 */
class PortalClassroomController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Classroom::class);

        $data = $this->validated($request);

        $classroom = Classroom::create($data);
        $classroom->setBanner($request->integer('banner_media_id') ?: null);

        return redirect()->route('portal.classes.feed', $classroom);
    }

    public function update(Request $request, Classroom $classroom): RedirectResponse
    {
        $this->authorize('update', $classroom);

        $classroom->update($this->validated($request));

        // Only touch the cover when the field was actually submitted, so a plain
        // rename never silently drops the banner.
        if ($request->has('banner_media_id')) {
            $classroom->setBanner($request->integer('banner_media_id') ?: null);
        }

        return back();
    }

    public function destroy(Request $request, Classroom $classroom): RedirectResponse
    {
        $this->authorize('delete', $classroom);

        // Archive rather than delete: a room holds a year of feed, chat and
        // reports that families may still want to read.
        $classroom->update(['is_archived' => true]);

        return redirect()->route('portal.home');
    }

    /**
     * Validated class attributes. `banner_media_id` is applied separately — it
     * lives in the mediables pivot, not on the row.
     *
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        return Arr::except($request->validate([
            'name' => ['required', 'string', 'max:120'],
            'grade' => ['nullable', 'string', 'max:60'],
            'year' => ['required', 'string', 'max:20'],
            // The cover is a real image from the media library, attached through
            // the mediables pivot rather than stored on the row.
            'banner_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'teacher_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(
                    fn ($q) => $q->whereIn('user_type', [User::TEACHER, User::ADMIN]),
                ),
            ],
        ]), ['banner_media_id']);
    }
}
