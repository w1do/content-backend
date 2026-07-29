<?php

use App\Infrastructure\Persistence\Eloquent\Category;
use App\Infrastructure\Persistence\Eloquent\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can filter products by slug', function () {
    $category = Category::factory()->create();

    $product1 = Product::factory()->create(['slug' => 'test-product-1']);
    $product2 = Product::factory()->create(['slug' => 'test-product-2']);
    $product3 = Product::factory()->create(['slug' => 'other-product']);

    $product1->categories()->attach($category);
    $product2->categories()->attach($category);
    $product3->categories()->attach($category);

    // Filter by single slug
    $response = $this->getJson('/api/v1/products?filter[slug]=test-product-1');
    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', 'test-product-1');

    // Filter by multiple slugs
    $response = $this->getJson('/api/v1/products?filter[slug]=test-product-1,test-product-2');
    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.slug', 'test-product-1')
        ->assertJsonPath('data.1.slug', 'test-product-2');
});
