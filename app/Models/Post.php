<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A class-feed post — broadcast to every guardian of every child in the room.
 * Photos attach via the `mediables` pivot (collection: "photos").
 *
 * @property int $id
 * @property int $classroom_id
 * @property int $user_id
 * @property string $body
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['classroom_id', 'user_id', 'body'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, HasMedia;

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
