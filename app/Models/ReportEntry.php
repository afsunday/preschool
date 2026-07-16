<?php

namespace App\Models;

use App\Support\Upload;
use Database\Factories\ReportEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One event on a child's day. A single table with a `type` — naps, meals and
 * nappies differ only in which fields they use.
 *
 * @property int $id
 * @property int $daily_report_id
 * @property string $type
 * @property string|null $label
 * @property Carbon|null $occurred_at
 * @property Carbon|null $ended_at
 * @property string|null $detail
 * @property string|null $note
 * @property list<string>|null $photos
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['daily_report_id', 'type', 'label', 'occurred_at', 'ended_at', 'detail', 'note', 'photos'])]
class ReportEntry extends Model
{
    /** @use HasFactory<ReportEntryFactory> */
    use HasFactory;

    /** The kinds of event a day is made of. */
    public const TYPES = ['nap', 'meal', 'nappy', 'mood', 'note', 'photo'];

    /**
     * What `detail` may say, per type. The teacher picks one — the old quick-log
     * hardcoded "Ate all" and "Wet", which meant half the entries were wrong.
     * Types absent here carry no detail.
     *
     * @var array<string, list<string>>
     */
    public const DETAILS = [
        'meal' => ['Ate all', 'Ate most', 'Ate some', 'Refused'],
        'nappy' => ['Wet', 'Soiled', 'Dry', 'Nappy rash'],
        // A mood is logged at a time like anything else — a child is not one
        // mood all day.
        'mood' => ['Happy', 'Content', 'Tired', 'Upset', 'Unwell'],
    ];

    /** Suggested `label` values per type. Free text is allowed too. */
    public const LABELS = [
        'meal' => ['Breakfast', 'Morning snack', 'Lunch', 'Afternoon snack', 'Tea'],
        'nappy' => ['Nappy change', 'Potty', 'Toilet'],
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'ended_at' => 'datetime',
            'photos' => 'array',
        ];
    }

    /**
     * Public URLs for the stored paths.
     *
     * @return list<string>
     */
    public function photoUrls(): array
    {
        return array_values(array_filter(array_map(
            fn (string $p) => Upload::url($p),
            $this->photos ?? [],
        )));
    }

    /** @return BelongsTo<DailyReport, $this> */
    public function report(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class, 'daily_report_id');
    }
}
