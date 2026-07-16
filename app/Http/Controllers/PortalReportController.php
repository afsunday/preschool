<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\DailyReport;
use App\Models\ReportEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * The daily report — naps, meals, nappies, mood, photos.
 *
 * A teacher fills it in across the day; it stays a draft until published, at
 * which point the child's guardians can see it.
 */
class PortalReportController extends Controller
{
    /** Set the mood/summary for a child's day, creating the report on demand. */
    public function update(Request $request, Child $child): RedirectResponse
    {
        $report = $this->reportFor($request, $child);

        $data = $request->validate([
            'mood' => ['nullable', 'string', Rule::in(['happy', 'ok', 'sad', 'tired'])],
            'summary' => ['nullable', 'string', 'max:2000'],
        ]);

        $report->update($data);

        return back();
    }

    /** Add one event (a nap, a meal, a nappy, a note, a photo) to the day. */
    public function addEntry(Request $request, Child $child): RedirectResponse
    {
        $report = $this->reportFor($request, $child);

        $data = $request->validate([
            'type' => ['required', Rule::in(ReportEntry::TYPES)],
            'occurred_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:occurred_at'],
            'detail' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
            'photos' => ['array', 'max:10'],
            'photos.*' => ['integer', 'exists:media,id'],
        ]);

        $entry = $report->entries()->create([
            'type' => $data['type'],
            'occurred_at' => $data['occurred_at'] ?? now(),
            'ended_at' => $data['ended_at'] ?? null,
            'detail' => $data['detail'] ?? null,
            'note' => $data['note'] ?? null,
        ]);

        foreach ($data['photos'] ?? [] as $i => $mediaId) {
            $entry->attachMedia((int) $mediaId, 'photos', $i);
        }

        return back();
    }

    public function removeEntry(Request $request, Child $child, ReportEntry $entry): RedirectResponse
    {
        $report = $this->reportFor($request, $child);
        abort_unless($entry->daily_report_id === $report->id, 404);

        $entry->delete();

        return back();
    }

    /** Send the day to the parents. */
    public function publish(Request $request, Child $child): RedirectResponse
    {
        $this->reportFor($request, $child)->publish();

        return back();
    }

    /**
     * The child's report for the given day, created if the teacher is only just
     * starting it. Staff-only: a parent never writes a report.
     */
    protected function reportFor(Request $request, Child $child): DailyReport
    {
        $classroom = $child->classroom;
        abort_if($classroom === null, 404);
        $this->authorize('staff', $classroom);

        $date = $request->date('date') ?? Carbon::today();

        return DailyReport::firstOrCreate(
            ['child_id' => $child->id, 'date' => $date->toDateString()],
            ['created_by' => $request->user()->id],
        );
    }
}
