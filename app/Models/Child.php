<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use Database\Factories\ChildFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
 * @property string|null $notes
 * @property string|null $invite_code
 * @property-read string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['classroom_id', 'first_name', 'last_name', 'dob', 'notes', 'invite_code'])]
class Child extends Model
{
    /** @use HasFactory<ChildFactory> */
    use HasFactory, HasMedia, SoftDeletes;

    protected $table = 'children';

    protected function casts(): array
    {
        return ['dob' => 'date'];
    }

    protected $appends = ['name'];

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
