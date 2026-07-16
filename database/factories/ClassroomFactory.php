<?php

namespace Database\Factories;

use App\Models\Classroom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Classroom>
 */
class ClassroomFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Mr '.fake()->lastName(),
            'grade' => fake()->randomElement(['Creche', 'Toddlers', 'Grade 1', 'Grade 2']),
            'year' => '2026/2027',
            'teacher_id' => null,
            'color' => fake()->randomElement(['#159cb0', '#f0a020', '#7c5cff', '#e8618c']),
            'is_archived' => false,
        ];
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => ['is_archived' => true]);
    }
}
