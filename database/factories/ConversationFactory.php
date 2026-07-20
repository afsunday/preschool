<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'classroom_id' => Classroom::factory(),
            'type' => Conversation::TYPE_DIRECT,
            'last_message_at' => null,
        ];
    }

    /** A direct thread whose sole participant is the given guardian. */
    public function forGuardian(User $guardian): static
    {
        return $this->afterCreating(
            fn (Conversation $conversation) => $conversation->participants()->attach($guardian->id),
        );
    }

    /** The class-wide announcement thread. */
    public function announcement(): static
    {
        return $this->state(['type' => Conversation::TYPE_ANNOUNCEMENT]);
    }
}
