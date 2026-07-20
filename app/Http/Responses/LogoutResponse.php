<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

/**
 * After logging out, send the user to the login page. It's an Inertia page, so
 * a normal redirect is fine — no bounce through the public (Blade) homepage that
 * would land in Inertia's modal.
 */
class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request): RedirectResponse|JsonResponse
    {
        return $request->wantsJson()
            ? new JsonResponse('', 204)
            : redirect()->route('login');
    }
}
