<?php

use App\Application\Jobs\GenerateSeoJob;
use App\Application\Services\SeoGenerator;
use App\Infrastructure\Persistence\Eloquent\Category;
use App\Infrastructure\Persistence\Eloquent\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

it('dispatches GenerateSeoJob when a category is created', function () {
    Bus::fake();

    $category = Category::factory()->create();

    Bus::assertDispatched(GenerateSeoJob::class, function ($job) use ($category) {
        return $job->model->is($category);
    });
});

it('dispatches GenerateSeoJob when a product is created', function () {
    Bus::fake();

    $product = Product::factory()->create();

    Bus::assertDispatched(GenerateSeoJob::class, function ($job) use ($product) {
        return $job->model->is($product);
    });
});

it('generates seo for a category', function () {
    Bus::fake();

    $category = Category::factory()->create([
        'name' => 'Test Category',
        'description' => 'This is a test category description.',
    ]);

    $mockGenerator = mock(SeoGenerator::class);
    $mockGenerator->shouldReceive('generateFromText')
        ->once()
        ->with('This is a test category description.')
        ->andReturn([
            'title' => 'Generated Title',
            'description' => 'Generated Description',
        ]);

    $job = new GenerateSeoJob($category);
    app()->call([$job, 'handle'], ['seoGenerator' => $mockGenerator]);

    $category->refresh();
    expect($category->seo)->not->toBeNull()
        ->and($category->seo->title)->toBe('Generated Title')
        ->and($category->seo->description)->toBe('Generated Description');
});

it('does not overwrite existing seo', function () {
    Bus::fake();

    $category = Category::factory()->create([
        'name' => 'Test Category',
    ]);

    // Create SEO BEFORE running the job
    $category->seo()->updateOrCreate([], [
        'title' => 'Manual Title',
        'description' => 'Manual Description',
        'is_indexable' => true,
    ]);

    $mockGenerator = mock(SeoGenerator::class);
    $mockGenerator->shouldReceive('generateFromText')->never();

    $job = new GenerateSeoJob($category);
    app()->call([$job, 'handle'], ['seoGenerator' => $mockGenerator]);

    $category->refresh();
    expect($category->seo->title)->toBe('Manual Title')
        ->and($category->seo->description)->toBe('Manual Description');
});

it('uses name as fallback for text extraction', function () {
    Bus::fake();

    $product = Product::factory()->create([
        'name' => 'Test Product',
        'description' => null,
    ]);

    $mockGenerator = mock(SeoGenerator::class);
    $mockGenerator->shouldReceive('generateFromText')
        ->once()
        ->with('Test Product')
        ->andReturn([
            'title' => 'Fallback Title',
            'description' => 'Fallback Description',
        ]);

    $job = new GenerateSeoJob($product);
    app()->call([$job, 'handle'], ['seoGenerator' => $mockGenerator]);

    $product->refresh();
    expect($product->seo->title)->toBe('Fallback Title');
});
