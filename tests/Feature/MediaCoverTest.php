<?php

use App\Infrastructure\Persistence\Eloquent\Category;
use App\Infrastructure\Persistence\Eloquent\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('product returns cover_url in api response', function () {
    Storage::fake('media');

    $category = Category::factory()->create();
    $product = Product::factory()->create();
    $product->categories()->attach($category);

    // Add image to main collection
    $product->addMedia(UploadedFile::fake()->image('main.jpg'))
        ->toMediaCollection('main');

    $response = $this->getJson("/api/v1/products/{$product->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $product->id)
        ->assertJsonStructure(['data' => ['cover_url']]);

    $coverUrl = $response->json('data.cover_url');
    expect($coverUrl)->not->toBeNull()
        ->and($coverUrl)->toContain('main')
        ->and($coverUrl)->toContain('cover');
});

test('category returns cover_url in api response', function () {
    Storage::fake('media');

    $category = Category::factory()->create();

    // Add image to cover collection
    $category->addMedia(UploadedFile::fake()->image('cover.jpg'))
        ->toMediaCollection('cover');

    $response = $this->getJson("/api/v1/categories/{$category->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $category->id)
        ->assertJsonStructure(['data' => ['cover_url']]);

    $coverUrl = $response->json('data.cover_url');
    expect($coverUrl)->not->toBeNull()
        ->and($coverUrl)->toContain('cover.jpg');
});
