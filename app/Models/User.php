<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'user_type',
        'email',
        'password',
        'permissions',
    ];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'has_admin_access' => 'boolean',
            'is_super' => 'boolean',
            'permissions' => 'array',
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
    // A `parent` (family) or `staff` (employee); `has_admin_access` opens the
    // back office; parent-ness is having a linked child. Any combination holds.

    public const PARENT = 'parent';

    public const STAFF = 'staff';

    public function isStaff(): bool
    {
        return $this->user_type === self::STAFF;
    }

    public function isAdmin(): bool
    {
        return (bool) $this->has_admin_access;
    }

    public function isParent(): bool
    {
        return $this->children()->exists();
    }

    public function homePath(): string
    {
        return $this->isAdmin() ? '/dashboard' : '/portal';
    }

    // ---- back-office permissions ------------------------------------------
    // Assigned directly to the user as a JSON array; `is_super` grants all.

    public function isSuper(): bool
    {
        return (bool) $this->is_super;
    }

    public function hasPermission(string $name): bool
    {
        return $this->isSuper() || in_array($name, $this->permissions ?? [], true);
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
