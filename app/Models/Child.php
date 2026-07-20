<?php

namespace App\Models;

use App\Support\Upload;
use Database\Factories\ChildFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * A child on the roster.
 *
 * Deliberately NOT a user: no login, no password. This record exists so the app
 * knows which parents belong to which room — it is the edge, not an account.
 *
 * @property int $id
 * @property int|null $classroom_id
 * @property string $first_name
 * @property string $last_name
 * @property Carbon|null $dob
 * @property string|null $photo_path
 * @property string|null $notes
 * @property string|null $invite_code
 * @property-read string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Child extends Model
{
    /** @use HasFactory<ChildFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'children';

    protected $fillable = ['classroom_id', 'first_name', 'last_name', 'dob', 'photo_path', 'notes', 'invite_code'];

    protected function casts(): array
    {
        return ['dob' => 'date'];
    }

    protected $appends = ['name'];

    /** Where the child's picture actually lives, if they have one. */
    public function photoUrl(): ?string
    {
        return Upload::url($this->photo_path);
    }

    protected function name(): Attribute
    {
        return Attribute::get(fn (): string => trim("{$this->first_name} {$this->last_name}"));
    }

    /** @return BelongsTo<Classroom, $this> */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * The parents/guardians linked to this child.
     *
     * @return BelongsToMany<User, $this>
     */
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'child_guardian')
            ->withPivot('relationship')
            ->withTimestamps();
    }

    /** @return HasMany<DailyReport, $this> */
    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class);
    }

    /** @return HasMany<ReportCard, $this> */
    public function reportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class)->latest('issued_on');
    }

    /** @return HasMany<Enrollment, $this> */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class)->latest('started_on');
    }

    /**
     * Place the child in a room. Any open enrollment is closed first, so a child
     * is only ever "currently in" one class — while the history of every room
     * they've passed through is kept. classroom_id stays as the denormalised
     * current room so the rest of the portal (feed, chats, reports) is unchanged.
     */
    public function enrollInto(Classroom $classroom): Enrollment
    {
        $open = $this->enrollments()->whereNull('ended_on')->get();

        if ($open->count() === 1 && $open->first()->classroom_id === $classroom->id) {
            return $open->first();
        }

        // A child placed before the enrolment log existed has a current room but
        // no open row — record that stay before moving on, so history stays whole.
        if ($open->isEmpty() && $this->classroom_id !== null && $this->classroom_id !== $classroom->id) {
            $this->enrollments()->create([
                'classroom_id' => $this->classroom_id,
                'started_on' => $this->created_at?->toDateString(),
                'ended_on' => now(),
            ]);
        }

        $this->enrollments()->whereNull('ended_on')->update(['ended_on' => now()]);
        $enrollment = $this->enrollments()->create([
            'classroom_id' => $classroom->id,
            'started_on' => now(),
        ]);
        $this->update(['classroom_id' => $classroom->id]);

        return $enrollment;
    }

    /** Take the child out of their current room, keeping the history intact. */
    public function removeFromClass(): void
    {
        $this->enrollments()->whereNull('ended_on')->update(['ended_on' => now()]);
        $this->update(['classroom_id' => null]);
    }

    /**
     * Mint the code a parent redeems to link themselves to this child. The same
     * code works for both parents — that is the whole invitation system.
     */
    public function refreshInviteCode(): string
    {
        $this->forceFill(['invite_code' => Str::upper(Str::random(8))])->save();

        return (string) $this->invite_code;
    }
}
