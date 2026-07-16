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
            'guardian_id' => User::factory()->parent(),
            'last_message_at' => null,
            'teacher_read_at' => null,
            'guardian_read_at' => null,
        ];
    }
}
