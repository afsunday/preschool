<?php

namespace Database\Factories;

use App\Models\DailyReport;
use App\Models\ReportEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportEntry>
 */
class ReportEntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'daily_report_id' => DailyReport::factory(),
            'type' => 'note',
            'occurred_at' => now(),
            'ended_at' => null,
            'detail' => null,
            'note' => fake()->sentence(),
        ];
    }

    public function nap(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'nap',
            'occurred_at' => today()->setTime(12, 30),
            'ended_at' => today()->setTime(14, 15),
        ]);
    }

    public function meal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'meal',
            'occurred_at' => today()->setTime(12, 0),
            'detail' => fake()->randomElement(['Ate all', 'Ate some', 'Refused']),
        ]);
    }

    public function nappy(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'nappy',
            'occurred_at' => today()->setTime(11, 20),
            'detail' => fake()->randomElement(['Wet', 'Dry', 'Soiled']),
        ]);
    }
}
