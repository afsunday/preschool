<?php

namespace App\Models;

use App\Support\Upload;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    protected $fillable = [
        'classroom_id',
        'user_id',
        'type',
        'body',
        'event_title',
        'event_at',
        'event_ends_at',
        'event_location',
        'photos',
    ];

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
