<?php

namespace App\Infrastructure\Caching\Invalidators;

use App\Infrastructure\Caching\CacheServiceInterface;
use App\Infrastructure\Persistence\Eloquent\Product;

class ProductCacheInvalidator
{
    public function __construct(
        private CacheServiceInterface $cache
    ) {}

    /**
     * Handle the Product "saved" event.
     */
    public function saved(Product $product): void
    {
        $this->invalidate();
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        $this->invalidate();
    }

    /**
     * Invalidate the product cache.
     */
    private function invalidate(): void
    {
        $this->cache->invalidate([
            (string) config('caching.tags.product', 'products'),
        ]);
    }
}
