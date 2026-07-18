<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;

/**
 * After confirming their email, land the user on their own home (admins the back
 * office, everyone else the portal).
 */
class VerifyEmailResponse implements VerifyEmailResponseContract
{
    public function toResponse($request): JsonResponse|RedirectResponse
    {
        /** @var Request $request */
        $home = $request->user()?->homePath() ?? '/portal';

        return $request->wantsJson()
            ? new JsonResponse('', 202)
            : redirect()->intended($home.'?verified=1');
    }
}
