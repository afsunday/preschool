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

        $data = $this->validated($request);

        $classroom = Classroom::create($data);

        return redirect()->route('portal.classes.feed', $classroom);
    }

    public function update(Request $request, Classroom $classroom): RedirectResponse
    {
        $this->authorize('update', $classroom);

        $classroom->update($this->validated($request));

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
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        return $request->validate([
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
            'teacher_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(
                    fn ($q) => $q->whereIn('user_type', [User::TEACHER, User::ADMIN]),
                ),
            ],
        ]);
    }
}
