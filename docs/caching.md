# Redis Caching Infrastructure

## Overview
Implemented a transparent caching layer for Category and Product entities using Redis. The implementation follows the Decorator pattern to keep caching logic separate from core business logic.

## Architecture
- **Decorator Pattern**: `CachedCategoryRepository` and `CachedProductRepository` wrap the original Eloquent repositories.
- **Tag-based Invalidation**: Uses Laravel's cache tags (supported by Redis) to group and clear related cache entries.
- **Event-driven Synchronization**: Eloquent Observers (`CategoryCacheInvalidator`, `ProductCacheInvalidator`) ensure the cache is cleared whenever models are saved or deleted, even outside of repository calls.
- **Graceful Fallback**: The system automatically falls back to database calls if the cache driver is unavailable or misconfigured.

## Components
- `App\Infrastructure\Caching\CacheServiceInterface`: Abstraction for caching operations.
- `App\Infrastructure\Caching\RedisCacheService`: Implementation using Laravel's Cache facade with tag support.
- `App\Infrastructure\Caching\KeyGenerator`: Ensures consistent and unique keys across the application.
- `App\Infrastructure\Caching\Invalidators\*`: Model observers for automatic cache clearing.

## Configuration
Controlled via `config/caching.php` and environment variables:
- `CACHE_ENABLED`: Global toggle for caching.
- `CACHE_CATEGORY_TTL`: Time-to-live for category data (seconds).
- `CACHE_PRODUCT_TTL`: Time-to-live for product data (seconds).

## Testing
Feature tests in `tests/Feature/Infrastructure/Caching/` verify:
1. Data is cached after the first read.
2. Subsequent reads hit the cache (no DB queries).
3. Cache is correctly invalidated when data changes.
