<?php

namespace App\Models;

use Database\Factories\DailyReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * One child's day: naps, meals, nappies, mood, photos.
 *
 * Stays a private draft until `published_at` is set — a teacher fills it in
 * across the day and sends it to the parent once.
 *
 * @property int $id
 * @property int $child_id
 * @property Carbon $date
 * @property string|null $mood
 * @property string|null $summary
 * @property Carbon|null $published_at
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['child_id', 'date', 'mood', 'summary', 'published_at', 'created_by'])]
class DailyReport extends Model
{
    /** @use HasFactory<DailyReportFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'published_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Child, $this> */
    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    /** @return HasMany<ReportEntry, $this> */
    public function entries(): HasMany
    {
        return $this->hasMany(ReportEntry::class)->orderBy('occurred_at');
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }

    public function publish(): void
    {
        $this->forceFill(['published_at' => now()])->save();
    }

    /** @param Builder<DailyReport> $query */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at');
    }
}
