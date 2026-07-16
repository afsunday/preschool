<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use Database\Factories\ClassroomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * A class/room — "Mr James · Grade 1 · 2026/2027".
 *
 * The cover is a real image from the media library (Google Classroom style),
 * attached through the `mediables` pivot rather than a column — so it needs no
 * schema of its own and "where is this image used?" keeps working.
 *
 * @property int $id
 * @property string $name
 * @property string|null $grade
 * @property string $year
 * @property int|null $teacher_id
 * @property string|null $color
 * @property bool $is_archived
 * @property-read string $label
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'grade', 'year', 'teacher_id', 'color', 'is_archived'])]
class Classroom extends Model
{
    /** @use HasFactory<ClassroomFactory> */
    use HasFactory, HasMedia;

    /** The media collection holding the class cover. */
    public const BANNER = 'banner';

    protected function casts(): array
    {
        return ['is_archived' => 'boolean'];
    }

    protected $appends = ['label'];

    /** The class cover, if one has been chosen. */
    public function banner(): ?Media
    {
        return $this->getMedia(self::BANNER)->first();
    }

    /**
     * Replace the cover — a class has exactly one, so the old one detaches.
     */
    public function setBanner(?int $mediaId): void
    {
        $this->media()->wherePivot('collection', self::BANNER)->detach();

        if ($mediaId !== null) {
            $this->attachMedia($mediaId, self::BANNER);
        }
    }

    /**
     * "Mr James · Grade 1 · 2026/2027" — how the class reads in the switcher.
     */
    protected function label(): Attribute
    {
        return Attribute::get(fn (): string => collect([$this->name, $this->grade, $this->year])
            ->filter()
            ->implode(' · '));
    }

    /** @return BelongsTo<User, $this> */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /** @return HasMany<Child, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(Child::class);
    }

    /** @return HasMany<Post, $this> */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /** @return HasMany<Conversation, $this> */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Every guardian of every child in this room — i.e. who a post reaches.
     *
     * @return Collection<int, User>
     */
    public function guardians(): Collection
    {
        return User::query()
            ->whereHas('children', fn (Builder $q) => $q->where('children.classroom_id', $this->id))
            ->get();
    }

    /**
     * Is this user a guardian of any child in this room? (How a parent reaches
     * a class at all — always through their own child.)
     */
    public function hasGuardian(User $user): bool
    {
        return $this->children()
            ->whereHas('guardians', fn (Builder $q) => $q->whereKey($user->id))
            ->exists();
    }

    /** @param Builder<Classroom> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_archived', false);
    }

    /**
     * The rooms a user may see: admins see all, teachers see the rooms they run,
     * parents see the rooms their children are in.
     *
     * @param  Builder<Classroom>  $query
     */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        $query->where(function (Builder $q) use ($user): void {
            $q->where('teacher_id', $user->id)
                ->orWhereHas('children.guardians', fn (Builder $g) => $g->whereKey($user->id));
        });
    }
}
