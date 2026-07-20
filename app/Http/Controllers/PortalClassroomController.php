<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\ClassroomBanner;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        [$data, $teacherIds] = $this->validated($request);

        $classroom = Classroom::create($data);
        $classroom->teachers()->sync($teacherIds);

        return redirect()->route('portal.classes.feed', $classroom);
    }

    public function update(Request $request, Classroom $classroom): RedirectResponse
    {
        $this->authorize('update', $classroom);

        [$data, $teacherIds] = $this->validated($request);

        $classroom->update($data);
        $classroom->teachers()->sync($teacherIds);

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

    public function restore(Request $request, Classroom $classroom): RedirectResponse
    {
        $this->authorize('update', $classroom);

        $classroom->update(['is_archived' => false]);

        return back();
    }

    /**
     * Returns the classroom attributes and the list of teacher ids separately —
     * the ids go to the pivot, not the classrooms table.
     *
     * @return array{0: array<string, mixed>, 1: list<int>}
     */
    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'grade' => ['nullable', 'string', 'max:60'],
            'year' => ['required', 'string', 'max:20'],
            // A key into the generated banner library — never a colour or a
            // class. Omitting it is fine: the column defaults. But anything sent
            // must name a banner we actually generate.
            'banner' => ['sometimes', 'string', function ($attr, $value, $fail) {
                if (! ClassroomBanner::valid($value)) {
                    $fail('That banner is not one we offer.');
                }
            }],
            'teacher_ids' => ['nullable', 'array'],
            'teacher_ids.*' => [
                Rule::exists('users', 'id')->where(
                    fn ($q) => $q->where('user_type', User::STAFF),
                ),
            ],
        ]);

        $teacherIds = array_map('intval', $data['teacher_ids'] ?? []);
        unset($data['teacher_ids']);

        // Keep the legacy single column pointed at the first teacher for anything
        // that still reads it; the pivot holds the full set.
        $data['teacher_id'] = $teacherIds[0] ?? null;

        return [$data, $teacherIds];
    }
}
