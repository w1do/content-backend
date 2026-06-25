<?php

namespace Database\Factories;

use App\Domain\Enums\ContentType;
use App\Infrastructure\Persistence\Eloquent\Content;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Content>
 */
class ContentFactory extends Factory
{
    protected $model = Content::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        return [
            'type' => $this->faker->randomElement(ContentType::cases()),
            'name' => $name,
            'slug' => Str::slug($name),
            'short_text' => $this->faker->sentence(),
            'full_text' => $this->faker->paragraphs(3, true),
            'views' => $this->faker->numberBetween(0, 1000),
            'tags' => $this->faker->words(3),
        ];
    }
}
