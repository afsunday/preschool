<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\ClassroomBanner;
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
            'banner' => fake()->randomElement(ClassroomBanner::keys()),
            'is_archived' => false,
        ];
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => ['is_archived' => true]);
    }

    public function configure(): static
    {
        // Mirror the real controller: a room's teacher also lives in the pivot.
        return $this->afterCreating(function (Classroom $classroom) {
            if ($classroom->teacher_id) {
                $classroom->teachers()->syncWithoutDetaching([$classroom->teacher_id]);
            }
        });
    }
}
