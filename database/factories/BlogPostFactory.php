<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BlogPostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'seobot_id' => $this->faker->unique()->uuid(),
            'title' => $this->faker->sentence(),
            'slug' => $this->faker->unique()->slug(),
            'excerpt' => $this->faker->sentence(),
            'content_html' => '<p>'.$this->faker->paragraph().'</p>',
            'meta_title' => $this->faker->sentence(),
            'meta_description' => $this->faker->sentence(),
            'keywords' => [$this->faker->word(), $this->faker->word()],
            'category' => $this->faker->word(),
            'tags' => [$this->faker->word(), $this->faker->word()],
            'cover_image_url' => $this->faker->imageUrl(),
            'published_at' => now(),
            'status' => 'published',
        ];
    }
}
