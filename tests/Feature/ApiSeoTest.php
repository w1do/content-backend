<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Category;
use App\Infrastructure\Persistence\Eloquent\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_api_contains_seo(): void
    {
        /** @var Category $category */
        $category = Category::factory()->create();
        $category->seo()->updateOrCreate([], [
            'title' => 'Test SEO Title',
            'description' => 'Test SEO Description',
            'is_indexable' => true,
        ]);

        $response = $this->getJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.seo.title', 'Test SEO Title')
            ->assertJsonPath('data.seo.description', 'Test SEO Description');
    }

    public function test_product_api_contains_seo(): void
    {
        /** @var Product $product */
        $product = Product::factory()->create();
        $product->seo()->updateOrCreate([], [
            'title' => 'Product SEO Title',
            'description' => 'Product SEO Description',
            'is_indexable' => false,
        ]);

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.seo.title', 'Product SEO Title')
            ->assertJsonPath('data.seo.description', 'Product SEO Description')
            ->assertJsonPath('data.seo.is_indexable', false);
    }
}
