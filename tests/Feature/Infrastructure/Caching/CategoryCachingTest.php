<?php

use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('CategoryRepository caches data and invalidates on change', function () {
    // Enable caching in config for test
    config(['caching.enabled' => true]);
    // Ensure we use a driver that supports tags (array does)
    config(['cache.default' => 'array']);

    $repository = app(CategoryRepositoryInterface::class);

    // 1. Create data
    $category = Category::factory()->create(['name' => 'Test Category']);

    // 2. First read - should hit DB
    DB::enableQueryLog();
    DB::flushQueryLog();
    $result1 = $repository->findById($category->id);

    expect(count(DB::getQueryLog()))->toBeGreaterThan(0);
    expect($result1->name)->toBe('Test Category');

    // 3. Second read - should hit Cache (no DB queries)
    DB::flushQueryLog();
    $result2 = $repository->findById($category->id);

    expect(count(DB::getQueryLog()))->toBe(0);
    expect($result2->name)->toBe('Test Category');

    // 4. Update category via Eloquent (triggers observer)
    $category->name = 'Updated Category';
    $category->save();

    // 5. Third read - should hit DB again because cache was invalidated
    DB::flushQueryLog();
    $result3 = $repository->findById($category->id);

    expect(count(DB::getQueryLog()))->toBeGreaterThan(0);
    expect($result3->name)->toBe('Updated Category');
});

test('CategoryRepository findTree is cached', function () {
    config(['caching.enabled' => true]);
    config(['cache.default' => 'array']);

    $repository = app(CategoryRepositoryInterface::class);
    Category::factory()->count(3)->create();

    // First call
    DB::enableQueryLog();
    DB::flushQueryLog();
    $repository->findTree();
    expect(count(DB::getQueryLog()))->toBeGreaterThan(0);

    // Second call
    DB::flushQueryLog();
    $repository->findTree();
    expect(count(DB::getQueryLog()))->toBe(0);
});
