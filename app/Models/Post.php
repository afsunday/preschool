<?php

namespace App\Models;

use App\Support\Upload;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A class-feed post — broadcast to every guardian of every child in the room.
 * Photos are plain uploads: the row holds their paths.
 *
 * @property int $id
 * @property int $classroom_id
 * @property int $user_id
 * @property string $body
 * @property list<string>|null $photos
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['classroom_id', 'user_id', 'body', 'photos'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['photos' => 'array'];
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
}
