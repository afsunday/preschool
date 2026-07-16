<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
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
 * @property Carbon|null $occurred_at
 * @property Carbon|null $ended_at
 * @property string|null $detail
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['daily_report_id', 'type', 'occurred_at', 'ended_at', 'detail', 'note'])]
class ReportEntry extends Model
{
    /** @use HasFactory<ReportEntryFactory> */
    use HasFactory, HasMedia;

    /** The kinds of event a day is made of. */
    public const TYPES = ['nap', 'meal', 'nappy', 'note', 'photo'];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<DailyReport, $this> */
    public function report(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class, 'daily_report_id');
    }
}
