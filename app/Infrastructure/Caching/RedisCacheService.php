<?php

namespace App\Infrastructure\Caching;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class RedisCacheService implements CacheServiceInterface
{
    /**
     * {@inheritDoc}
     */
    public function remember(string $key, array $tags, Closure $callback, ?int $ttl = null): mixed
    {
        if (! config('caching.enabled', true)) {
            return $callback();
        }

        try {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        } catch (Throwable $e) {
            Log::error('Cache error: '.$e->getMessage(), [
                'key' => $key,
                'tags' => $tags,
                'exception' => $e,
            ]);

            return $callback();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function invalidate(array $tags): void
    {
        try {
            Cache::tags($tags)->flush();
        } catch (Throwable $e) {
            Log::error('Cache invalidation error: '.$e->getMessage(), [
                'tags' => $tags,
                'exception' => $e,
            ]);
        }
    }
}
