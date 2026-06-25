<?php

use App\Infrastructure\Persistence\Eloquent\Category;
use App\Infrastructure\Persistence\Eloquent\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('products API does not allow creation', function () {
    $response = $this->postJson('/api/v1/products', [
        'name' => 'Test Product',
        'slug' => 'test-product',
    ]);

    $response->assertStatus(405);
});

test('products API does not allow update', function () {
    $product = Product::factory()->create();

    $response = $this->putJson("/api/v1/products/{$product->id}", [
        'name' => 'Updated Name',
    ]);

    $response->assertStatus(405);
});

test('products API does not allow deletion', function () {
    $product = Product::factory()->create();

    $response = $this->deleteJson("/api/v1/products/{$product->id}");

    $response->assertStatus(405);
});

test('categories API does not allow creation', function () {
    $response = $this->postJson('/api/v1/categories', [
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    $response->assertStatus(405);
});

test('categories API does not allow update', function () {
    $category = Category::factory()->create();

    $response = $this->putJson("/api/v1/categories/{$category->id}", [
        'name' => 'Updated Name',
    ]);

    $response->assertStatus(405);
});

test('categories API does not allow deletion', function () {
    $category = Category::factory()->create();

    $response = $this->deleteJson("/api/v1/categories/{$category->id}");

    $response->assertStatus(405);
});

test('GET endpoints still work for products', function () {
    $product = Product::factory()->create();

    $this->getJson('/api/v1/products')->assertStatus(200);
    $this->getJson("/api/v1/products/{$product->id}")->assertStatus(200);
});

test('GET endpoints still work for categories', function () {
    $category = Category::factory()->create();

    $this->getJson('/api/v1/categories')->assertStatus(200);
    $this->getJson("/api/v1/categories/{$category->id}")->assertStatus(200);
    $this->getJson('/api/v1/categories/tree')->assertStatus(200);
});
