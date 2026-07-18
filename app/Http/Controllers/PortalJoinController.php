<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Linking a parent to a child.
 *
 * This is the entire parent↔child relationship system: an admin hands out the
 * child's invite code, the parent redeems it. No search-for-your-child, no
 * approval queue. A parent with three kids redeems three codes — the same code
 * works for both parents of one child.
 */
class PortalJoinController extends Controller
{
    public function show(Request $request): Response
    {
        return Inertia::render('portal/join', [
            'children' => $request->user()->children()->with('classroom')->get()
                ->map(fn ($child) => [
                    'id' => $child->id,
                    'name' => $child->name,
                    'classroom' => $child->classroom?->label,
                ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:12'],
            'relationship' => ['required', Rule::in(['mum', 'dad', 'guardian'])],
        ]);

        $user = $request->user();
        $child = $user->claimChild(Str::upper(trim($data['code'])), $data['relationship']);

        if ($child === null) {
            throw ValidationException::withMessages([
                'code' => "That code doesn't match any child. Check it with the school.",
            ]);
        }

        // Linking the child is what makes this person a parent — there is no
        // separate flag to set. A staff member who redeems their own child's
        // code simply becomes a parent too.

        return $child->classroom_id === null
            ? redirect()->route('portal.home')
            : redirect()->route('portal.classes.today', $child->classroom_id);
    }
}
