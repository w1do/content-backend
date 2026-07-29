<?php

namespace App\Infrastructure\Caching;

class KeyGenerator
{
    /**
     * Generate a cache key based on the entity, method name and its arguments.
     */
    public function generate(string $entity, string $method, array $args = []): string
    {
        $hash = ! empty($args) ? md5(serialize($args)) : 'all';

        return sprintf('%s:%s:%s', $entity, $method, $hash);
    }
}
