<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Classroom;
use App\Models\Conversation;
use App\Models\DailyReport;
use App\Models\ReportCard;
use App\Models\ReportEntry;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The daycare portal: classes, feed, roster, daily reports, chat.
 *
 * Every screen is addressed to an adult — staff or guardian. Children have
 * records here, never accounts.
 */
class PortalController extends Controller
{
    /**
     * The classes this user can see. Staff see the rooms they run; a parent sees
     * the rooms their children are in (which is how one parent lands in two).
     */
    public function home(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('portal/home', [
            'classes' => $this->classList($user),
            'children' => $user->isParent()
                ? $user->children()->with('classroom')->get()
                    ->map(fn (Child $c) => $this->childSummary($c, $user))
                    ->values()
                : null,
            'isStaff' => $user->isStaff(),
            'canManage' => $user->can('create', Classroom::class),
            // Only an admin ever opens the create-class form.
            'teachers' => $user->isAdmin()
                ? User::query()
                    ->whereIn('user_type', [User::TEACHER, User::ADMIN])
                    ->orderBy('first_name')
                    ->get()
                    ->map(fn (User $t) => ['id' => $t->id, 'name' => $t->name])
                    ->values()
                : [],
        ]);
    }

    public function feed(Request $request, Classroom $classroom): Response
    {
        $this->authorize('view', $classroom);

        $posts = $classroom->posts()
            ->with('author')
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn ($post) => [
                'id' => $post->id,
                'body' => $post->body,
                'author' => $post->author->name,
                'createdAt' => $post->created_at?->diffForHumans(),
                'photos' => $post->photoUrls(),
            ]);

        return Inertia::render('portal/class/feed', [
            ...$this->classProps($request, $classroom),
            'posts' => $posts,
        ]);
    }

    /** The roster. Guardians are shown so staff know who to talk to. */
    public function students(Request $request, Classroom $classroom): Response
    {
        $this->authorize('view', $classroom);

        $user = $request->user();
        $canSeeAll = $user->can('staff', $classroom);

        $children = $classroom->children()
            ->with(['guardians', 'reportCards'])
            ->orderBy('first_name')
            ->get()
            // A parent sees the roster, but only their own child's guardians and
            // invite code — never another family's.
            ->map(fn (Child $c) => $this->childSummary($c, $user, $canSeeAll));

        return Inertia::render('portal/class/students', [
            ...$this->classProps($request, $classroom),
            'children' => $children,
            'canManage' => $user->isAdmin(),
        ]);
    }

    /** Today's reports — the thing a parent actually opens the app for. */
    public function today(Request $request, Classroom $classroom): Response
    {
        $this->authorize('view', $classroom);

        $user = $request->user();
        $date = $request->date('date') ?? Carbon::today();
        $isStaff = $user->can('staff', $classroom);

        $children = $classroom->children()
            ->when(! $isStaff, fn ($q) => $q->whereHas('guardians', fn ($g) => $g->whereKey($user->id)))
            ->with(['dailyReports' => fn ($q) => $q->whereDate('date', $date)->with('entries')])
            ->orderBy('first_name')
            ->get()
            ->map(function (Child $child) use ($isStaff) {
                /** @var DailyReport|null $report */
                $report = $child->dailyReports->first();

                // A draft is staff-only: a parent sees nothing until it's sent.
                if ($report !== null && ! $report->isPublished() && ! $isStaff) {
                    $report = null;
                }

                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'photo' => $child->photoUrl(),
                    'report' => $report === null ? null : [
                        'id' => $report->id,
                        'summary' => $report->summary,
                        'published' => $report->isPublished(),
                        'entries' => $report->entries->map(fn ($e) => [
                            'id' => $e->id,
                            'type' => $e->type,
                            'label' => $e->label,
                            'detail' => $e->detail,
                            'note' => $e->note,
                            'at' => $e->occurred_at?->format('H:i'),
                            'until' => $e->ended_at?->format('H:i'),
                            'photos' => $e->photoUrls(),
                        ])->values(),
                    ],
                ];
            });

        return Inertia::render('portal/class/today', [
            ...$this->classProps($request, $classroom),
            'date' => $date->toDateString(),
            'children' => $children,
            'isStaff' => $isStaff,
            // The pickers are driven by the model, so the form can only offer
            // what the controller will accept.
            'options' => [
                'details' => ReportEntry::DETAILS,
                'labels' => ReportEntry::LABELS,
            ],
        ]);
    }

    /**
     * Chat. Staff see every thread in the room; a guardian sees only their own.
     */
    public function chats(Request $request, Classroom $classroom, ?Conversation $conversation = null): Response
    {
        $this->authorize('view', $classroom);

        $user = $request->user();
        $isStaff = $user->can('staff', $classroom);

        $threads = $classroom->conversations()
            ->when(! $isStaff, fn ($q) => $q->where('guardian_id', $user->id))
            ->with('guardian')
            ->orderByDesc('last_message_at')
            ->get();

        // A guardian opening chat should land in their own thread, creating it on
        // demand — no "start a conversation" step.
        if ($conversation === null && ! $isStaff) {
            $conversation = $threads->first() ?? $classroom->conversations()->create([
                'guardian_id' => $user->id,
            ]);
            $threads = collect([$conversation->load('guardian')]);
        }

        if ($conversation !== null) {
            abort_unless($conversation->classroom_id === $classroom->id, 404);
            abort_unless($isStaff || $conversation->guardian_id === $user->id, 403);
            $conversation->markReadFor($user);
        }

        return Inertia::render('portal/class/chats', [
            ...$this->classProps($request, $classroom),
            'threads' => $threads->map(fn (Conversation $c) => [
                'id' => $c->id,
                'guardian' => $c->guardian->name,
                'lastMessageAt' => $c->last_message_at?->diffForHumans(),
                'unread' => $c->isUnreadFor($user),
            ])->values(),
            'active' => $conversation === null ? null : [
                'id' => $conversation->id,
                'guardian' => $conversation->guardian->name,
                'messages' => $conversation->messages()
                    ->with('author')
                    ->orderBy('created_at')
                    ->limit(100)
                    ->get()
                    ->map(fn ($m) => [
                        'id' => $m->id,
                        'body' => $m->body,
                        'author' => $m->author->name,
                        'mine' => $m->user_id === $user->id,
                        'at' => $m->created_at?->diffForHumans(),
                        'photos' => $m->photoUrls(),
                    ])->values(),
            ],
            'isStaff' => $isStaff,
        ]);
    }

    // ---- shared serialisation ---------------------------------------------

    /**
     * Props every class screen needs: the switcher list + the active room.
     *
     * @return array<string, mixed>
     */
    protected function classProps(Request $request, Classroom $classroom): array
    {
        $user = $request->user();

        return [
            'classes' => $this->classList($user),
            'classroom' => [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'label' => $classroom->label,
                'grade' => $classroom->grade,
                'year' => $classroom->year,
                'color' => $classroom->color,
                'banner' => $classroom->banner,
                'teacher' => $classroom->teacher?->name,
                'teacherId' => $classroom->teacher_id,
                'childCount' => $classroom->children()->count(),
            ],
            'canPost' => $user->can('staff', $classroom),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function classList(User $user)
    {
        return Classroom::query()
            ->visibleTo($user)
            ->active()
            ->withCount('children')
            ->with('teacher')
            ->orderBy('name')
            ->get()
            ->map(fn (Classroom $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'label' => $c->label,
                'grade' => $c->grade,
                'year' => $c->year,
                'color' => $c->color,
                'banner' => $c->banner,
                'teacher' => $c->teacher?->name,
                'teacherId' => $c->teacher_id,
                'childCount' => $c->children_count,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function childSummary(Child $child, User $user, bool $canSeeAll = true): array
    {
        $isMine = $child->guardians->contains('id', $user->id);
        $visible = $canSeeAll || $isMine;

        return [
            'id' => $child->id,
            'name' => $child->name,
            'photo' => $child->photoUrl(),
            'classroom' => $child->classroom?->label,
            'classroomId' => $child->classroom_id,
            'isMine' => $isMine,
            // Another family's guardians and invite code are never exposed.
            'guardians' => $visible
                ? $child->guardians->map(fn (User $g) => [
                    'id' => $g->id,
                    'name' => $g->name,
                    'relationship' => $g->pivot?->relationship,
                ])->values()
                : [],
            'inviteCode' => $user->isAdmin() ? $child->invite_code : null,
            'reportCards' => $this->reportCardsFor($child, $user, $canSeeAll),
        ];
    }

    /**
     * A child's report cards. Staff see every card including unshared ones; a
     * guardian sees only their own child's, and only once shared.
     *
     * @return Collection<int, array<string, mixed>>
     */
    protected function reportCardsFor(Child $child, User $user, bool $isStaff)
    {
        $isMine = $child->guardians->contains('id', $user->id);

        if (! $isStaff && ! $isMine) {
            return collect();
        }

        return $child->reportCards
            ->filter(fn (ReportCard $card) => $isStaff || $card->isPublished())
            ->map(fn (ReportCard $card) => [
                'id' => $card->id,
                'title' => $card->title,
                'issuedOn' => $card->issued_on?->toDateString(),
                'note' => $card->note,
                'published' => $card->isPublished(),
                'file' => [
                    'url' => route('portal.report-cards.download', [$child, $card]),
                    'name' => $card->original_name,
                    'size' => $card->size,
                ],
            ])
            ->values();
    }
}
