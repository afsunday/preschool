<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The families directory: every parent account and the children they guard.
 * Read-only — families are created by parents themselves (public registration)
 * and linked to children by redeeming an invite code.
 */
class ParentController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('parents/index', [
            'parents' => User::query()
                ->where('user_type', User::PARENT)
                ->with(['children' => fn ($q) => $q->with('classroom')->orderBy('first_name')])
                ->orderBy('first_name')
                ->get()
                ->map(fn (User $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'email' => $p->email,
                    'verified' => $p->email_verified_at !== null,
                    'joinedAt' => $p->created_at?->diffForHumans(),
                    'children' => $p->children->map(fn (Child $c) => [
                        'id' => $c->id,
                        'name' => $c->name,
                        'classroom' => $c->classroom?->label,
                    ])->values(),
                ]),
        ]);
    }
}
