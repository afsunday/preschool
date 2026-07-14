<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'disk' => 'public',
            'path' => 'media/'.$this->faker->uuid().'.jpg',
            'filename' => $this->faker->uuid().'.jpg',
            'original_name' => $name.'.jpg',
            'extension' => 'jpg',
            'mime_type' => 'image/jpeg',
            'kind' => 'image',
            'size' => $this->faker->numberBetween(10_000, 2_000_000),
            'width' => 800,
            'height' => 600,
            'title' => ucfirst($name),
            'alt' => null,
            'description' => null,
        ];
    }
}
