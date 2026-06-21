<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'parent_id' => null,
            'name' => $name,
            'slug' => Str::slug($name),
            'status' => 'active',
            'description' => $this->faker->paragraph(),
        ];
    }
}
