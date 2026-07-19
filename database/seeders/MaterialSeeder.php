<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\MaterialCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Family Tips',
            'Learning Activities',
            'Educational Videos',
            'Health & Wellness',
            'Arts & Craft',
            'School Readiness',
        ];

        $byName = [];

        foreach ($categories as $i => $name) {
            $byName[$name] = MaterialCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'position' => $i],
            );
        }

        // Lifted from the old resources_programs block, now real rows.
        $materials = [
            ['Arts & Craft', 'download', true, '/images/about/program-art.jpg',
                'At-Home Craft Kit: 12 Simple, Mess-Free Art Activities for Toddlers (Printable PDF)'],
            ['Educational Videos', 'video', true, '/images/about/program-drawing.jpg',
                'Watch: Fun Drawing Games That Build Fine Motor Skills at Home'],
            ['School Readiness', 'download', true, '/images/about/program-classroom.jpg',
                'Kindergarten Readiness Checklist: What Your 4-5 Year Old Should Know Before Big School (PDF)'],
            ['Family Tips', 'article', false, '/images/about/gallery-8.jpg',
                'The rise of artificial intelligence in the educational sector'],
            ['Family Tips', 'article', true, '/images/about/gallery-3.jpg',
                '5 Gentle Ways to Ease Separation Anxiety on Drop-Off Mornings'],
            ['Family Tips', 'article', true, '/images/about/gallery-4.jpg',
                "Turning Tantrums Into Teachable Moments: A Family's Guide to Big Feelings"],
            ['Learning Activities', 'article', false, '/images/about/testimonial.jpg',
                'Why Play Is Serious Learning for Children Under 5'],
            ['Health & Wellness', 'article', false, '/images/about/gallery-2.jpg',
                'Building Healthy Sleep Routines for Toddlers and Preschoolers'],
            ['Learning Activities', 'article', false, '/images/about/gallery-6.jpg',
                'Screen-Free Activities to Keep Little Minds Busy and Growing'],
        ];

        foreach ($materials as $i => [$category, $type, $featured, $image, $title]) {
            Material::updateOrCreate(
                ['title' => $title],
                [
                    'category_id' => $byName[$category]->id,
                    'type' => $type,
                    'url' => '#',
                    'image_path' => $image,
                    'is_featured' => $featured,
                    'position' => $i,
                    'published_at' => now(),
                ],
            );
        }
    }
}
