<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('staff/index', [
            'staff' => User::query()
                ->where('user_type', User::ADMIN)
                ->orderBy('first_name')
                ->get()
                ->map(fn (User $u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'firstName' => $u->first_name,
                    'lastName' => $u->last_name,
                    'email' => $u->email,
                    'isSuper' => $u->is_super,
                    'permissions' => $u->permissions ?? [],
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

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $data = $request->safe();

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? '',
            'email' => $data['email'],
            'password' => $data['password'],
            'user_type' => User::ADMIN,
            'permissions' => $data['permissions'] ?? [],
        ]);

        // Created by an admin — no email-verification hoop.
        $user->forceFill(['email_verified_at' => now()])->save();

        return back()->with('success', __('Staff member added.'));
    }

    public function update(UpdateStaffRequest $request, User $user): RedirectResponse
    {
        $data = $request->safe();

        $user->fill([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? '',
            'email' => $data['email'],
            'permissions' => $data['permissions'] ?? [],
        ]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return back()->with('success', __('Staff member updated.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        // You can't lock yourself out.
        abort_if($user->id === $this->userId(), 403);

        $user->delete();

        return back()->with('success', __('Staff member removed.'));
    }

    protected function userId(): ?int
    {
        return request()->user()?->id;
    }
}
