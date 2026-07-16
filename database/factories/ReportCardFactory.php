<?php

namespace Database\Factories;

use App\Models\Child;
use App\Models\ReportCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportCard>
 */
class ReportCardFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'child_id' => Child::factory(),
            'title' => 'Term 1 · 2026/2027',
            'issued_on' => today(),
            'note' => null,
            'path' => 'report-cards/1/'.fake()->uuid().'.pdf',
            'original_name' => 'report.pdf',
            'mime_type' => 'application/pdf',
            'size' => 128_000,
            'published_at' => null,
            'created_by' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => ['published_at' => now()]);
    }
}
