<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\ReportCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Report cards — the termly document a parent actually keeps.
 *
 * Deliberately does NOT touch the media library: that is for the public site's
 * CMS. These are uploaded straight to a private disk and streamed back through
 * an authorised route, so a report card is never a guessable public URL.
 */
class PortalReportCardController extends Controller
{
    public function store(Request $request, Child $child): RedirectResponse
    {
        $this->authorizeStaff($child);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'issued_on' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ]);

        $file = $request->file('file');

        // Foldered per child so the disk stays navigable, with a random name so
        // the path can't be guessed from the child's id alone.
        $path = $file->store("report-cards/{$child->id}", ReportCard::DISK);

        $child->reportCards()->create([
            'title' => $data['title'],
            'issued_on' => $data['issued_on'] ?? null,
            'note' => $data['note'] ?? null,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'created_by' => $request->user()->id,
        ]);

        return back();
    }

    /**
     * Stream the file to anyone allowed to see it: staff of the room, or a
     * guardian of this child once it's shared.
     */
    public function download(Request $request, Child $child, ReportCard $card): StreamedResponse
    {
        abort_unless($card->child_id === $child->id, 404);

        $classroom = $child->classroom;
        abort_if($classroom === null, 404);

        $user = $request->user();
        $isStaff = $user->can('staff', $classroom);
        $isGuardian = $child->guardians()->whereKey($user->id)->exists();

        // A guardian may only open it once shared; nobody else may open it at all.
        abort_unless($isStaff || ($isGuardian && $card->isPublished()), 403);
        abort_unless($card->exists(), 404);

        return Storage::disk(ReportCard::DISK)->download($card->path, $card->original_name);
    }

    /** Rename, re-date, or share/unshare. */
    public function update(Request $request, Child $child, ReportCard $card): RedirectResponse
    {
        $this->authorizeStaff($child);
        abort_unless($card->child_id === $child->id, 404);

        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:120'],
            'issued_on' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
            'published' => ['sometimes', 'boolean'],
        ]);

        $card->update(collect($data)->except('published')->all());

        if ($request->has('published')) {
            $request->boolean('published') ? $card->publish() : $card->unpublish();
        }

        return back();
    }

    public function destroy(Request $request, Child $child, ReportCard $card): RedirectResponse
    {
        $this->authorizeStaff($child);
        abort_unless($card->child_id === $child->id, 404);

        // The file belongs to this row alone — nothing else can reference it, so
        // it goes with it rather than being orphaned on disk.
        $card->deleteFile();
        $card->delete();

        return back();
    }

    /** A card belongs to a child, and a child belongs to exactly one room. */
    protected function authorizeStaff(Child $child): void
    {
        $classroom = $child->classroom;
        abort_if($classroom === null, 404);

        $this->authorize('staff', $classroom);
    }
}
