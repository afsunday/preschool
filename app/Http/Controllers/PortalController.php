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
            'archivedClasses' => $user->can('create', Classroom::class)
                ? $this->classList($user, archived: true)
                : [],
            'children' => ($user->user_type === User::PARENT || $user->isParent())
                ? $user->children()->with('classroom')->get()
                    ->map(fn (Child $c) => $this->childSummary($c, $user))
                    ->values()
                : null,
            'isStaff' => $user->isStaff(),
            'canManage' => $user->can('create', Classroom::class),
            'teachers' => $user->isAdmin()
                ? User::query()
                    ->where('user_type', User::STAFF)
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

        $user = $request->user();

        $posts = $classroom->posts()
            ->with(['author', 'comments' => fn ($q) => $q->with('author')->oldest()])
            ->withCount([
                'likers',
                'likers as liked_by_me' => fn ($q) => $q->whereKey($user->id),
            ])
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn ($post) => [
                'id' => $post->id,
                'body' => $post->body,
                'author' => $post->author->name,
                'createdAt' => $post->created_at?->diffForHumans(),
                'photos' => $post->photoUrls(),
                'type' => $post->type,
                'event' => $post->type === 'event' && $post->event_at ? [
                    'title' => $post->event_title,
                    'month' => $post->event_at->format('M'),
                    'day' => $post->event_at->format('j'),
                    'dateLabel' => $post->event_at->format('D j M'),
                    'timeLabel' => $post->event_ends_at
                        ? $post->event_at->format('g:i A').' – '.$post->event_ends_at->format('g:i A')
                        : $post->event_at->format('g:i A'),
                    'location' => $post->event_location,
                ] : null,
                'likesCount' => $post->likers_count,
                'likedByMe' => (bool) $post->liked_by_me,
                'comments' => $post->comments->map(fn ($c) => [
                    'id' => $c->id,
                    'author' => $c->author->name,
                    'body' => $c->body,
                    'at' => $c->created_at?->diffForHumans(),
                    'mine' => $c->user_id === $user->id,
                ])->values(),
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
     * Chat. Staff see every family in the room and can start any thread; a
     * guardian only ever sees and posts to their own.
     */
    public function chats(Request $request, Classroom $classroom, ?Conversation $conversation = null): Response
    {
        $this->authorize('view', $classroom);

        $user = $request->user();
        $isStaff = $user->can('staff', $classroom);

        // The class-wide announcement thread always exists.
        $announcement = $classroom->conversations()->firstOrCreate(['type' => Conversation::TYPE_ANNOUNCEMENT]);

        // Staff picking a family (?guardian=) start the thread on demand — this is
        // what lets a teacher reach out to a family that hasn't messaged first.
        if ($isStaff && $conversation === null && $request->filled('guardian')) {
            $guardianId = (int) $request->query('guardian');
            abort_unless(
                $classroom->children()->whereHas('guardians', fn ($q) => $q->whereKey($guardianId))->exists(),
                404,
            );
            $conversation = $this->directThreadFor($classroom, $guardianId);
        }

        // A guardian lands in their own thread, created on demand.
        if ($conversation === null && ! $isStaff) {
            $conversation = $this->directThreadFor($classroom, $user->id);
        }

        if ($conversation !== null) {
            abort_unless($conversation->classroom_id === $classroom->id, 404);
            // Staff reach every thread in their room; a guardian sees only their own
            // thread and the announcement.
            abort_unless(
                $isStaff
                    || $conversation->hasParticipant($user)
                    || ($conversation->isAnnouncement() && $classroom->hasGuardian($user)),
                403,
            );
        }

        return Inertia::render('portal/class/chats', [
            ...$this->classProps($request, $classroom),
            'families' => $this->threadList($classroom, $user, $isStaff, $announcement),
            'active' => $conversation === null ? null : [
                'id' => $conversation->id,
                'guardian' => $conversation->isAnnouncement()
                    ? 'Class announcements'
                    : $conversation->participants->firstWhere('id', '!=', $user->id)?->name
                        ?? $conversation->participants->first()?->name,
                'announcement' => $conversation->isAnnouncement(),
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

    /**
     * A family's direct thread with the room's staff, created on demand. The
     * guardian is the thread's only named participant; any staff on the room may
     * reply, which keeps one thread per family even with several co-teachers.
     */
    protected function directThreadFor(Classroom $classroom, int $guardianId): Conversation
    {
        $conversation = $classroom->conversations()
            ->where('type', Conversation::TYPE_DIRECT)
            ->whereHas('participants', fn ($q) => $q->whereKey($guardianId))
            ->first();

        if ($conversation === null) {
            $conversation = $classroom->conversations()->create(['type' => Conversation::TYPE_DIRECT]);
            $conversation->participants()->attach($guardianId);
        }

        return $conversation;
    }

    /**
     * Every family in a room — each guardian with their thread, if one exists.
     * Families who've never messaged still appear (with a null conversation), so
     * staff can start a chat with anyone.
     *
     * @return Collection<int, array<string, mixed>>
     */
    protected function roomFamilies(Classroom $classroom): Collection
    {
        // Direct threads keyed by their guardian participant.
        $threads = $classroom->conversations()
            ->where('type', Conversation::TYPE_DIRECT)
            ->with('participants:id')
            ->get()
            ->keyBy(fn (Conversation $c) => $c->participants->first()?->id);

        return $classroom->guardians()
            ->sortBy('first_name')
            ->map(fn (User $g) => [
                'guardianId' => $g->id,
                'name' => $g->name,
                'conversationId' => $threads->get($g->id)?->id,
                'lastMessageAt' => $threads->get($g->id)?->last_message_at?->diffForHumans(),
                'isAnnouncement' => false,
            ])
            ->values();
    }

    /**
     * The chat sidebar: the class announcement first, then the direct threads —
     * every family for staff, or just the guardian's own for a parent.
     *
     * @return Collection<int, array<string, mixed>>
     */
    protected function threadList(Classroom $classroom, User $user, bool $isStaff, Conversation $announcement): Collection
    {
        $items = collect([[
            'guardianId' => 0,
            'name' => 'Class announcements',
            'conversationId' => $announcement->id,
            'lastMessageAt' => $announcement->last_message_at?->diffForHumans(),
            'isAnnouncement' => true,
        ]]);

        if ($isStaff) {
            return $items->concat($this->roomFamilies($classroom))->values();
        }

        $own = $classroom->conversations()
            ->where('type', Conversation::TYPE_DIRECT)
            ->whereHas('participants', fn ($q) => $q->whereKey($user->id))
            ->first();

        if ($own !== null) {
            $items->push([
                'guardianId' => $user->id,
                'name' => "{$classroom->name}'s room",
                'conversationId' => $own->id,
                'lastMessageAt' => $own->last_message_at?->diffForHumans(),
                'isAnnouncement' => false,
            ]);
        }

        return $items->values();
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
                'teachers' => $classroom->teachers
                    ->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])
                    ->values(),
                'childCount' => $classroom->children()->count(),
            ],
            'canPost' => $user->can('staff', $classroom),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public static function classList(User $user, bool $archived = false)
    {
        return Classroom::query()
            ->visibleTo($user)
            // Staff split their rooms into active vs archived; a family always
            // sees every room their child is in, even after it's archived.
            ->when($user->isStaff(), fn ($q) => $q->where('is_archived', $archived))
            ->withCount('children')
            ->with('teachers')
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
                'teachers' => $c->teachers
                    ->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])
                    ->values(),
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
