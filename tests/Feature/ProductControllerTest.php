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

test('can create a product', function () {
    $category = Category::factory()->create();
    $data = [
        'category_id' => $category->id,
        'name' => 'New Product',
        'slug' => 'new-product',
        'description' => 'New Description',
        'attributes' => ['key' => 'value'],
    ];

    $response = $this->postJson('/api/v1/products', $data);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'New Product');

    $this->assertDatabaseHas('products', [
        'name' => 'New Product',
        'slug' => 'new-product',
    ]);
});

test('can update a product', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create();
    $product->categories()->attach($category);

    $data = [
        'category_id' => $category->id,
        'name' => 'Updated Product',
        'slug' => 'updated-product',
        'description' => 'Updated Description',
        'attributes' => ['key' => 'updated'],
    ];

    $response = $this->putJson("/api/v1/products/{$product->id}", $data);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Updated Product');

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated Product',
    ]);
});

test('can delete a product', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create();
    $product->categories()->attach($category);

    $response = $this->deleteJson("/api/v1/products/{$product->id}");

    $response->assertStatus(204);

    $this->assertDatabaseMissing('products', [
        'id' => $product->id,
    ]);
});

test('fails to create product with invalid data', function () {
    $response = $this->postJson('/api/v1/products', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['category_id', 'name', 'slug']);
});

test('returns 404 for non-existent product', function () {
    $response = $this->getJson('/api/v1/products/999');

    $response->assertStatus(404);
});
