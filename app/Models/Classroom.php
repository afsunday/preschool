<?php

namespace App\Models;

use Database\Factories\ClassroomFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Classroom extends Model
{
    /** @use HasFactory<ClassroomFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'grade',
        'year',
        'teacher_id',
        'color',
        'banner',
        'is_archived',
    ];

    protected function casts(): array
    {
        return [
            'is_archived' => 'boolean',
        ];
    }

    protected $appends = ['label'];

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

    /**
     * Every teacher who runs this room. A room can have more than one; the pivot
     * is the source of truth, with `teacher_id` kept as a legacy fallback.
     *
     * @return BelongsToMany<User, $this>
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'classroom_teacher');
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
                ->orWhereHas('teachers', fn (Builder $t) => $t->whereKey($user->id))
                ->orWhereHas('children.guardians', fn (Builder $g) => $g->whereKey($user->id));
        });
    }
}
