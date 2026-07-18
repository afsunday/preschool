<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

/**
 * After login, send admins to the back office and everyone else to the portal.
 */
class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): JsonResponse|RedirectResponse
    {
        /** @var Request $request */
        $home = $request->user()?->homePath() ?? '/portal';

        return $request->wantsJson()
            ? new JsonResponse('', 200)
            : redirect()->intended($home);
    }
}
