<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(),
            'attributes' => [
                'Страна' => $this->faker->country(),
                'Бренд' => $this->faker->company(),
            ],
        ];
    }
}
