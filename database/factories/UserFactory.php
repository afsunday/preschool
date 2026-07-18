<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'user_type' => User::PARENT,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Two conveniences at make-time:
     *
     * 1. Legacy `user_type` values still work — a test that sets 'admin',
     *    'teacher' or 'user' is mapped onto the flag model, so fixtures that
     *    predate the roles refactor need no edits.
     * 2. A back-office account is a full-access owner unless a test opts out
     *    with `['is_super' => false]` (plus its own permissions).
     */
    public function configure(): static
    {
        return $this->afterMaking(function (User $user): void {
            match ($user->getAttributes()['user_type'] ?? null) {
                'admin' => $user->forceFill(['user_type' => User::STAFF, 'has_admin_access' => true]),
                'teacher' => $user->forceFill(['user_type' => User::STAFF, 'has_admin_access' => false]),
                'user' => $user->forceFill(['user_type' => User::PARENT]),
                default => null,
            };

            if ($user->has_admin_access
                && ! array_key_exists('is_super', $user->getAttributes())) {
                $user->is_super = true;
            }
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /** A back-office admin: staff with access (super by default, see configure). */
    public function admin(): static
    {
        return $this->state(fn () => ['user_type' => User::STAFF, 'has_admin_access' => true]);
    }

    /** A teacher: staff who runs rooms but has no back-office access. */
    public function teacher(): static
    {
        return $this->state(fn () => ['user_type' => User::STAFF, 'has_admin_access' => false]);
    }

    /** A family account. Parent-ness proper follows from a linked child. */
    public function parent(): static
    {
        return $this->state(fn () => ['user_type' => User::PARENT]);
    }
}
