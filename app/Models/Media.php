<?php

namespace App\Models;

use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'disk',
        'path',
        'filename',
        'original_name',
        'extension',
        'mime_type',
        'kind',
        'size',
        'width',
        'height',
        'title',
        'alt',
        'description',
        'uploaded_by',
    ];

    /**
     * Public URL to the stored file.
     */
    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function isImage(): bool
    {
        return $this->kind === 'image';
    }

    /**
     * Every model this media item is attached to (across all morphs).
     *
     * @return MorphToMany<Model, $this>
     */
    public function usages(): MorphToMany
    {
        // A generic morphedByMany is awkward across arbitrary types, so usage
        // is queried directly off the `mediables` pivot in the controller.
        return $this->morphToMany(self::class, 'mediable');
    }

    /**
     * @param  Builder<Media>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

        $query->where(function (Builder $q) use ($like): void {
            $q->where('title', 'like', $like)
                ->orWhere('alt', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('original_name', 'like', $like);
        });
    }

    /**
     * @param  Builder<Media>  $query
     */
    public function scopeKind(Builder $query, ?string $kind): void
    {
        if ($kind !== null && $kind !== '' && $kind !== 'all') {
            $query->where('kind', $kind);
        }
    }
}
