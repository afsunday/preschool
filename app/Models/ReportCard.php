<?php

namespace App\Models;

use Database\Factories\ReportCardFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * A termly report card for one child.
 *
 * Holds its own file path rather than a media reference: the media library is
 * for the public site's CMS, and a child's report belongs to one family, is
 * never reused, and must not appear in an asset picker.
 *
 * Hidden until `published_at` is set — a teacher uploads when it's ready and
 * shares when the school says so, which is rarely the same moment.
 *
 * @property int $id
 * @property int $child_id
 * @property string $title
 * @property Carbon|null $issued_on
 * @property string|null $note
 * @property string $path
 * @property string $original_name
 * @property string|null $mime_type
 * @property int $size
 * @property Carbon|null $published_at
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ReportCard extends Model
{
    /** @use HasFactory<ReportCardFactory> */
    use HasFactory;

    protected $fillable = [
        'child_id', 'title', 'issued_on', 'note',
        'path', 'original_name', 'mime_type', 'size',
        'published_at', 'created_by',
    ];

    /** Private, not `public` — a report card is never world-readable. */
    public const DISK = 'local';

    protected function casts(): array
    {
        return [
            'issued_on' => 'date',
            'published_at' => 'datetime',
            'size' => 'integer',
        ];
    }

    /** @return BelongsTo<Child, $this> */
    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function exists(): bool
    {
        return Storage::disk(self::DISK)->exists($this->path);
    }

    /** Remove the file from disk. Call before deleting the row. */
    public function deleteFile(): void
    {
        Storage::disk(self::DISK)->delete($this->path);
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }

    public function publish(): void
    {
        $this->forceFill(['published_at' => now()])->save();
    }

    public function unpublish(): void
    {
        $this->forceFill(['published_at' => null])->save();
    }

    /** @param Builder<ReportCard> $query */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at');
    }
}
