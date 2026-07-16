<?php

namespace Database\Factories;

use App\Models\Child;
use App\Models\DailyReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyReport>
 */
class DailyReportFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'child_id' => Child::factory(),
            'date' => today(),
            'mood' => fake()->randomElement(['happy', 'ok', 'tired']),
            'summary' => null,
            'published_at' => null,
            'created_by' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => ['published_at' => now()]);
    }
}
