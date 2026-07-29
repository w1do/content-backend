<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Infrastructure\Persistence\Eloquent\Category;
use App\Infrastructure\Persistence\Eloquent\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductFilteringTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_filter_products_by_category_id(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create();
        $product->categories()->attach($category);

        Product::factory()->count(2)->create(); // другие товары

        $response = $this->getJson("/api/v1/products?filter[category_id]={$category->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $product->id)
            ->assertJsonPath('data.0.category_id', $category->id);
    }

    public function test_can_filter_products_by_multiple_category_ids(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        $product1 = Product::factory()->create();
        $product1->categories()->attach($category1);

        $product2 = Product::factory()->create();
        $product2->categories()->attach($category2);

        Product::factory()->count(1)->create(); // другой товар

        $ids = "{$category1->id},{$category2->id}";
        $response = $this->getJson("/api/v1/products?filter[category_id]={$ids}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_products_by_category_name_partial(): void
    {
        $category = Category::factory()->create(['name' => 'Газовое оборудование']);
        $product = Product::factory()->create();
        $product->categories()->attach($category);

        Category::factory()->create(['name' => 'Электроника']);
        Product::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/products?filter[category_name]=Газовое');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $product->id);
    }

    public function test_can_filter_products_by_category_full_path(): void
    {
        $root = Category::factory()->create(['name' => 'Оборудование', 'slug' => 'oborudovanie']);
        $child = Category::factory()->create([
            'name' => 'Газовое',
            'slug' => 'gazovoe',
            'parent_id' => $root->id,
        ]);

        $child->refresh(); // Убеждаемся, что full_path сгенерирован

        $product = Product::factory()->create();
        $product->categories()->attach($child);

        Product::factory()->count(2)->create();

        $response = $this->getJson("/api/v1/products?filter[category_full_path]={$child->full_path}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $product->id)
            ->assertJsonPath('data.0.category_full_path', $child->full_path);
    }

    public function test_returns_all_products_when_no_filters_applied(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_combine_category_filters(): void
    {
        $category = Category::factory()->create(['name' => 'Котлы']);
        $product = Product::factory()->create();
        $product->categories()->attach($category);

        $otherCategory = Category::factory()->create(['name' => 'Трубы']);
        $otherProduct = Product::factory()->create();
        $otherProduct->categories()->attach($otherCategory);

        $response = $this->getJson("/api/v1/products?filter[category_id]={$category->id}&filter[category_name]=Котлы");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $product->id);

        // Проверяем противоречивые фильтры
        $response = $this->getJson("/api/v1/products?filter[category_id]={$category->id}&filter[category_name]=Трубы");
        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
}
