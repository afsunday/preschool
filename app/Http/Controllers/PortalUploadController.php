<?php

namespace App\Http\Controllers;

use App\Support\Upload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The portal's ordinary upload endpoint.
 *
 * The file lands the moment it is chosen, so the teacher's wait overlaps with
 * them typing rather than following it. The response carries a temp path that
 * the form submits; the receiving controller promotes it with Upload::keep().
 *
 * JSON, not Inertia — this is called from a file input, mid-form.
 */
class PortalUploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Any signed-in portal user may upload: a parent attaches photos to a
        // chat message too. What they may attach it *to* is checked on submit.
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,gif,heic,pdf'],
        ]);

        return response()->json(Upload::temp($request->file('file')));
    }
}
