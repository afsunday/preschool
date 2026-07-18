<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The team: everyone who works at the daycare, in one screen. A member is staff
 * — a teacher who can run rooms — and may also be granted back-office access,
 * either scoped to a set of permissions or full (super).
 */
class TeamController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('team/index', [
            'members' => User::query()
                ->where('user_type', User::STAFF)
                ->withCount('classrooms')
                ->orderBy('first_name')
                ->get()
                ->map(fn (User $u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'firstName' => $u->first_name,
                    'lastName' => $u->last_name,
                    'email' => $u->email,
                    'hasAdminAccess' => $u->has_admin_access,
                    'isSuper' => $u->is_super,
                    'permissions' => $u->permissions ?? [],
                    'classCount' => $u->classrooms_count,
                    'isSelf' => $u->id === $this->userId(),
                ]),
            'groups' => PermissionGroup::query()->ordered()->with('permissions')->get()
                ->map(fn (PermissionGroup $g) => [
                    'name' => $g->name,
                    'permissions' => $g->permissions->map(fn (Permission $p) => [
                        'name' => $p->name,
                        'label' => $p->display_name,
                    ])->all(),
                ])->all(),
        ]);
    }

    public function store(StoreTeamRequest $request): RedirectResponse
    {
        $data = $request->safe();

        $user = new User([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? '',
            'email' => $data['email'],
            'password' => $data['password'],
            'user_type' => User::STAFF,
        ]);

        $this->applyAccess($user, $data->toArray());

        // Created by an admin — no email-verification hoop.
        $user->email_verified_at = now();
        $user->save();

        return back()->with('success', __('Team member added.'));
    }

    public function update(UpdateTeamRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->isStaff(), 404);

        $data = $request->safe();

        $user->fill([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? '',
            'email' => $data['email'],
        ]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $this->applyAccess($user, $data->toArray());

        $user->save();

        return back()->with('success', __('Team member updated.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        // You can't lock yourself out.
        abort_if($user->id === $this->userId(), 403);
        abort_unless($user->isStaff(), 404);

        // Their rooms become unassigned (classrooms.teacher_id nullOnDelete).
        $user->delete();

        return back()->with('success', __('Team member removed.'));
    }

    /**
     * Set the back-office columns from the form. Without access there are no
     * permissions and no super flag, whatever was posted; full access clears
     * the scoped list.
     *
     * @param  array<string, mixed>  $data
     */
    protected function applyAccess(User $user, array $data): void
    {
        $hasAccess = (bool) ($data['has_admin_access'] ?? false);
        $isSuper = $hasAccess && (bool) ($data['is_super'] ?? false);

        $user->has_admin_access = $hasAccess;
        $user->is_super = $isSuper;
        $user->permissions = $hasAccess && ! $isSuper ? ($data['permissions'] ?? []) : [];
    }

    protected function userId(): ?int
    {
        return request()->user()?->id;
    }
}
