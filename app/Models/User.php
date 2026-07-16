<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $user_type
 * @property-read string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['first_name', 'last_name', 'user_type', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['name'];

    /**
     * The user's full name (first + last).
     */
    protected function name(): Attribute
    {
        return Attribute::get(fn (): string => trim("{$this->first_name} {$this->last_name}"));
    }

    // ---- roles -------------------------------------------------------------
    // The portal has exactly three kinds of person. Children are not among them:
    // they have records, not accounts.

    public const ADMIN = 'admin';

    public const TEACHER = 'teacher';

    public const PARENT = 'parent';

    public function isAdmin(): bool
    {
        return $this->user_type === self::ADMIN;
    }

    public function isTeacher(): bool
    {
        return $this->user_type === self::TEACHER;
    }

    public function isParent(): bool
    {
        return $this->user_type === self::PARENT;
    }

    /** Staff run rooms; admins can do anything a teacher can. */
    public function isStaff(): bool
    {
        return $this->isAdmin() || $this->isTeacher();
    }

    // ---- portal relationships ---------------------------------------------

    /**
     * Rooms this user teaches.
     *
     * @return HasMany<Classroom, $this>
     */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'teacher_id');
    }

    /**
     * The children this user is a guardian of. A parent can have several; this
     * is the "parent with more than one kid" case, and it needs no extra logic.
     *
     * @return BelongsToMany<Child, $this>
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(Child::class, 'child_guardian')
            ->withPivot('relationship')
            ->withTimestamps();
    }

    /**
     * Chat threads where this user is the parent side.
     *
     * @return HasMany<Conversation, $this>
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'guardian_id');
    }

    /**
     * Link this user to a child by redeeming the child's invite code. This is
     * the entire parent↔child relationship system.
     */
    public function claimChild(string $code, string $relationship = 'guardian'): ?Child
    {
        $child = Child::query()->where('invite_code', $code)->first();

        $child?->guardians()->syncWithoutDetaching([
            $this->id => ['relationship' => $relationship],
        ]);

        return $child;
    }
}
