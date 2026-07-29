<?php

namespace App\Infrastructure\Caching;

use Closure;

interface CacheServiceInterface
{
    /**
     * Cache the results of a closure.
     *
     * @param  array<string>  $tags
     */
    public function remember(string $key, array $tags, Closure $callback, ?int $ttl = null): mixed;

    /**
     * Invalidate cache by tags.
     *
     * @param  array<string>  $tags
     */
    public function invalidate(array $tags): void;
}
