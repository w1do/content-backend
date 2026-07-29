<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Infrastructure\Persistence\Eloquent\Category;
use App\Infrastructure\Persistence\Eloquent\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryIncludeProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_include_products_count_in_categories_list(): void
    {
        $category = Category::factory()->create();
        $products = Product::factory()->count(3)->create();

        foreach ($products as $product) {
            $product->categories()->attach($category);
        }

        $response = $this->getJson('/api/v1/categories?include=products');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $category->id)
            ->assertJsonPath('data.0.products_count', 3);
    }

    public function test_does_not_include_products_count_by_default(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create();
        $product->categories()->attach($category);

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $category->id)
            ->assertJsonMissingPath('data.0.products_count');
    }

    public function test_can_include_products_count_in_single_category(): void
    {
        $category = Category::factory()->create();
        $products = Product::factory()->count(2)->create();
        foreach ($products as $product) {
            $product->categories()->attach($category);
        }

        $response = $this->getJson("/api/v1/categories/{$category->id}?include=products");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $category->id)
            ->assertJsonPath('data.products_count', 2);
    }
}
