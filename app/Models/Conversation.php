<?php

namespace App\Models;

use Database\Factories\ConversationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A thread between a classroom and one guardian.
 *
 * Scoped to the *room*, not the teacher: when co-teachers arrive, any staff on
 * the room can reply with no schema change. A thread has exactly two sides, so
 * unread is two timestamps rather than a participants table.
 *
 * @property int $id
 * @property int $classroom_id
 * @property int $guardian_id
 * @property Carbon|null $last_message_at
 * @property Carbon|null $teacher_read_at
 * @property Carbon|null $guardian_read_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['classroom_id', 'guardian_id', 'last_message_at', 'teacher_read_at', 'guardian_read_at'])]
class Conversation extends Model
{
    /** @use HasFactory<ConversationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'teacher_read_at' => 'datetime',
            'guardian_read_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Classroom, $this> */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /** @return BelongsTo<User, $this> */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guardian_id');
    }

    /** @return HasMany<Message, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Unread from the given user's side of the thread.
     */
    public function isUnreadFor(User $user): bool
    {
        if ($this->last_message_at === null) {
            return false;
        }

        $readAt = $user->id === $this->guardian_id
            ? $this->guardian_read_at
            : $this->teacher_read_at;

        return $readAt === null || $readAt->lt($this->last_message_at);
    }

    public function markReadFor(User $user): void
    {
        $side = $user->id === $this->guardian_id ? 'guardian_read_at' : 'teacher_read_at';

        $this->forceFill([$side => now()])->save();
    }
}
