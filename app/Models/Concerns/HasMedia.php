<?php

namespace App\Models\Concerns;

use App\Models\Media;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Attach media to any model through the `mediables` pivot.
 *
 * @phpstan-require-extends Model
 */
trait HasMedia
{
    /**
     * @return MorphToMany<Media, $this>
     */
    public function media(): MorphToMany
    {
        return $this->morphToMany(Media::class, 'mediable')
            ->withPivot(['collection', 'position'])
            ->withTimestamps()
            ->orderBy('position');
    }

    /**
     * Attach a media item to a named collection (defaults to "default").
     */
    public function attachMedia(int $mediaId, string $collection = 'default', int $position = 0): void
    {
        $this->media()->syncWithoutDetaching([
            $mediaId => ['collection' => $collection, 'position' => $position],
        ]);
    }

    public function detachMedia(int $mediaId): void
    {
        $this->media()->detach($mediaId);
    }

    /**
     * @return Collection<int, Media>
     */
    public function getMedia(string $collection = 'default')
    {
        return $this->media()->wherePivot('collection', $collection)->get();
    }
}
