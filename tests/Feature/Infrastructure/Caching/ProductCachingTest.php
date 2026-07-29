<?php

use App\Domain\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('ProductRepository caches data and invalidates on change', function () {
    config(['caching.enabled' => true]);
    config(['cache.default' => 'array']);

    $repository = app(ProductRepositoryInterface::class);

    $product = Product::factory()->create(['name' => 'Test Product']);

    // First read
    DB::enableQueryLog();
    DB::flushQueryLog();
    $repository->findById($product->id);
    expect(count(DB::getQueryLog()))->toBeGreaterThan(0);

    // Second read
    DB::flushQueryLog();
    $repository->findById($product->id);
    expect(count(DB::getQueryLog()))->toBe(0);

    // Update
    $product->name = 'Updated Product';
    $product->save();

    // Third read
    DB::flushQueryLog();
    $result = $repository->findById($product->id);
    expect(count(DB::getQueryLog()))->toBeGreaterThan(0);
    expect($result->name)->toBe('Updated Product');
});
