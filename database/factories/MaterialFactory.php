<?php

namespace Database\Factories;

use App\Models\Material;
use App\Models\MaterialCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => MaterialCategory::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->optional()->paragraph(),
            'type' => fake()->randomElement(Material::TYPES),
            'url' => '#',
            'image_path' => '/images/about/gallery-'.fake()->numberBetween(1, 8).'.jpg',
            'is_featured' => false,
            'position' => 0,
            'published_at' => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['published_at' => null]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }
}
