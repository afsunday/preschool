<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\DailyReport;
use App\Models\ReportEntry;
use App\Support\Upload;
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
    /** Set the day's closing summary, creating the report on demand. */
    public function update(Request $request, Child $child): RedirectResponse
    {
        $report = $this->reportFor($request, $child);

        $data = $request->validate([
            'summary' => ['nullable', 'string', 'max:2000'],
        ]);

        $report->update($data);

        return back();
    }

    /** Add one event (a nap, a meal, a nappy, a note, a photo) to the day. */
    public function addEntry(Request $request, Child $child): RedirectResponse
    {
        $report = $this->reportFor($request, $child);
        $type = (string) $request->input('type');

        $data = $request->validate([
            'type' => ['required', Rule::in(ReportEntry::TYPES)],
            'label' => ['nullable', 'string', 'max:120'],
            'occurred_at' => ['nullable', 'date'],
            // A nap is the only entry with a span, and it cannot end before it
            // started.
            'ended_at' => ['nullable', 'date', 'after_or_equal:occurred_at'],
            // Meals and nappies choose from a fixed set; a note is free text.
            'detail' => array_key_exists($type, ReportEntry::DETAILS)
                ? ['required', Rule::in(ReportEntry::DETAILS[$type])]
                : ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
            'photos' => ['array', 'max:10'],
            'photos.*' => ['string'],
        ]);

        $report->entries()->create([
            'type' => $data['type'],
            'label' => $data['label'] ?? null,
            'occurred_at' => $data['occurred_at'] ?? now(),
            'ended_at' => $data['ended_at'] ?? null,
            'detail' => $data['detail'] ?? null,
            'note' => $data['note'] ?? null,
            'photos' => Upload::keepAll($data['photos'] ?? [], "reports/{$child->id}"),
        ]);

        return back();
    }

    public function removeEntry(Request $request, Child $child, ReportEntry $entry): RedirectResponse
    {
        $report = $this->reportFor($request, $child);
        abort_unless($entry->daily_report_id === $report->id, 404);

        Upload::removeAll($entry->photos);
        $entry->delete();

        return back();
    }

    /**
     * Open one child's day to their parents.
     *
     * This is a visibility gate, not a send: once open, anything logged after is
     * visible immediately, so there is no second "send".
     */
    public function publish(Request $request, Child $child): RedirectResponse
    {
        $this->reportFor($request, $child)->publish();

        return back();
    }

    /** Close it back to a draft — for a day opened by mistake. */
    public function unpublish(Request $request, Child $child): RedirectResponse
    {
        $this->reportFor($request, $child)->unpublish();

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

        // Deliberately not firstOrCreate: the `date` cast writes a full
        // timestamp, so matching on the bare 'Y-m-d' misses the row it just
        // wrote and the second entry of the day hits the unique index. whereDate
        // compares the date part, which is what the key actually means.
        $report = DailyReport::query()
            ->where('child_id', $child->id)
            ->whereDate('date', $date)
            ->first();

        return $report ?? DailyReport::create([
            'child_id' => $child->id,
            'date' => $date->toDateString(),
            'created_by' => $request->user()->id,
        ]);
    }
}
