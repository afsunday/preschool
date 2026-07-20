<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Classroom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The Students directory: a global list of every child, independent of any one
 * class. From here staff add students and enrol them into a room; a student
 * keeps their history as they move up year to year.
 */
class PortalStudentController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeStaff($request);

        $query = trim((string) $request->query('q', ''));

        $children = Child::query()
            ->with(['classroom', 'enrollments.classroom', 'guardians', 'reportCards'])
            ->withCount('guardians')
            ->when($query !== '', fn ($q) => $q->where(
                fn ($w) => $w->where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
            ))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn (Child $child) => $this->summary($child));

        return Inertia::render('portal/students/index', [
            'students' => $children,
            'classes' => $this->classOptions(),
            'query' => $query,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeStaff($request);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'dob' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
        ]);

        $child = Child::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'dob' => $data['dob'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        // Every student gets an invite code so a family can be linked later.
        $child->refreshInviteCode();

        if (! empty($data['classroom_id'])) {
            $child->enrollInto(Classroom::whereKey($data['classroom_id'])->firstOrFail());
        }

        return back();
    }

    public function update(Request $request, Child $child): RedirectResponse
    {
        $this->authorizeStaff($request);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'dob' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $child->update($data);

        return back();
    }

    public function enroll(Request $request, Child $child): RedirectResponse
    {
        $this->authorizeStaff($request);

        $data = $request->validate([
            'classroom_id' => ['required', 'exists:classrooms,id'],
        ]);

        $child->enrollInto(Classroom::whereKey($data['classroom_id'])->firstOrFail());

        return back();
    }

    public function unenroll(Request $request, Child $child): RedirectResponse
    {
        $this->authorizeStaff($request);

        $child->removeFromClass();

        return back();
    }

    /**
     * The directory is staff-only: a global roster of children is not something a
     * parent should ever see.
     */
    protected function authorizeStaff(Request $request): void
    {
        abort_unless($request->user()?->isStaff(), 403);
    }

    /** @return array<string, mixed> */
    protected function summary(Child $child): array
    {
        return [
            'id' => $child->id,
            'name' => $child->name,
            'firstName' => $child->first_name,
            'lastName' => $child->last_name,
            'dob' => $child->dob?->toDateString(),
            'age' => $child->dob?->age,
            'photo' => $child->photoUrl(),
            'notes' => $child->notes,
            'inviteCode' => $child->invite_code,
            'guardianCount' => $child->guardians_count,
            'guardians' => $child->guardians
                ->map(fn ($g) => [
                    'id' => $g->id,
                    'name' => $g->name,
                    'email' => $g->email,
                    'relationship' => $g->pivot->relationship,
                ])
                ->values(),
            'reportCards' => $child->reportCards
                ->map(fn ($card) => [
                    'id' => $card->id,
                    'title' => $card->title,
                    'issuedOn' => $card->issued_on?->toDateString(),
                    'published' => $card->isPublished(),
                    'file' => [
                        'url' => route('portal.report-cards.download', [$child, $card]),
                        'name' => $card->original_name,
                    ],
                ])
                ->values(),
            'currentClass' => $child->classroom ? [
                'id' => $child->classroom->id,
                'name' => $child->classroom->name,
                'year' => $child->classroom->year,
            ] : null,
            'history' => $child->enrollments
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'classroom' => $e->classroom?->name,
                    'year' => $e->classroom?->year,
                    'startedOn' => $e->started_on?->format('M Y'),
                    'endedOn' => $e->ended_on?->format('M Y'),
                    'current' => $e->ended_on === null,
                ])
                ->values(),
        ];
    }

    /**
     * Classes to enrol into, grouped for the picker by year (newest first) — this
     * is where "create a class each year" pays off.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function classOptions(): array
    {
        return Classroom::query()
            ->orderByDesc('year')
            ->orderBy('name')
            ->get()
            ->map(fn (Classroom $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'grade' => $c->grade,
                'year' => $c->year,
            ])
            ->values()
            ->all();
    }
}
