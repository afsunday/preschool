<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The portal's own profile screen. It renders in the portal chrome but posts to
 * the shared settings controllers (profile.update / user-password.update), so a
 * parent or teacher never lands in the admin shell to manage their account.
 */
class PortalSettingsController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('portal/settings', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }
}
