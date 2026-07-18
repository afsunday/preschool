<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

/**
 * A freshly registered parent lands in the portal, where they're prompted to
 * enter their child's invite code.
 */
class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request): JsonResponse|RedirectResponse
    {
        /** @var Request $request */
        $home = $request->user()?->homePath() ?? '/portal';

        return $request->wantsJson()
            ? new JsonResponse('', 201)
            : redirect($home);
    }
}
