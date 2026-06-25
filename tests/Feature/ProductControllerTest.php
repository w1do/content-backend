<?php

use App\Infrastructure\Persistence\Eloquent\Category;
use App\Infrastructure\Persistence\Eloquent\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can list products', function () {
    $category = Category::factory()->create();
    $products = Product::factory()->count(3)->create();
    foreach ($products as $product) {
        $product->categories()->attach($category);
    }

    $response = $this->getJson('/api/v1/products');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('can show a product', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create();
    $product->categories()->attach($category);

    $response = $this->getJson("/api/v1/products/{$product->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.name', $product->name)
        ->assertJsonPath('data.category_id', $category->id);
});

test('returns 404 for non-existent product', function () {
    $response = $this->getJson('/api/v1/products/999');

    $response->assertStatus(404);
});
