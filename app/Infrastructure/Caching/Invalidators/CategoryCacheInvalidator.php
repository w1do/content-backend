<?php

namespace App\Infrastructure\Caching\Invalidators;

use App\Infrastructure\Caching\CacheServiceInterface;
use App\Infrastructure\Persistence\Eloquent\Category;

class CategoryCacheInvalidator
{
    public function __construct(
        private CacheServiceInterface $cache
    ) {}

    /**
     * Handle the Category "saved" event.
     */
    public function saved(Category $category): void
    {
        $this->invalidate();
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        $this->invalidate();
    }

    /**
     * Invalidate the category cache.
     */
    private function invalidate(): void
    {
        $this->cache->invalidate([
            (string) config('caching.tags.category', 'categories'),
        ]);
    }
}
