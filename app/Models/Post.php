<?php

namespace App\Models;

use App\Support\Upload;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A class-feed post — broadcast to every guardian of every child in the room.
 * Photos are plain uploads: the row holds their paths.
 *
 * @property int $id
 * @property int $classroom_id
 * @property int $user_id
 * @property string $type
 * @property string $body
 * @property string|null $event_title
 * @property Carbon|null $event_at
 * @property Carbon|null $event_ends_at
 * @property string|null $event_location
 * @property list<string>|null $photos
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'classroom_id', 'user_id', 'type', 'body',
    'event_title', 'event_at', 'event_ends_at', 'event_location', 'photos',
])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    public const TYPE_UPDATE = 'update';

    public const TYPE_EVENT = 'event';

    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'event_at' => 'datetime',
            'event_ends_at' => 'datetime',
        ];
    }

    /**
     * Public URLs for the stored paths. The DB keeps paths so the disk can move
     * without a migration.
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

    /** @return BelongsTo<Classroom, $this> */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsToMany<User, $this> */
    public function likers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_likes')->withTimestamps();
    }

    /** @return HasMany<PostComment, $this> */
    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class);
    }
}
