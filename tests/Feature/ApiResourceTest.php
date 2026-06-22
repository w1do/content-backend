<?php

use App\Infrastructure\Persistence\Eloquent\Category;
use App\Infrastructure\Persistence\Eloquent\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('products index returns json resource collection', function () {
    $response = $this->getJson('/api/v1/products');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [],
        ]);
});

test('product show returns json resource', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create();
    $product->categories()->attach($category);

    $response = $this->getJson("/api/v1/products/{$product->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'category_id',
                'name',
                'slug',
                'description',
                'attributes',
            ],
        ])
        ->assertJson([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
            ],
        ]);
});

test('categories index returns json resource collection', function () {
    $response = $this->getJson('/api/v1/categories');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [],
        ]);
});

test('category show returns json resource', function () {
    $category = Category::factory()->create([
        'name' => 'Dummy Category',
    ]);

    $response = $this->getJson("/api/v1/categories/{$category->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'parent_id',
                'name',
                'slug',
                'status',
                'description',
            ],
        ])
        ->assertJson([
            'data' => [
                'id' => $category->id,
                'name' => 'Dummy Category',
            ],
        ]);
});
