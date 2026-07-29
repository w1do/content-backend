<?php

namespace App\Providers;

use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Domain\Repositories\ContentRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Domain\Repositories\PromptRepositoryInterface;
use App\Domain\Repositories\SeoRepositoryInterface;
use App\Domain\Services\ProductParserInterface;
use App\Infrastructure\Caching\CacheServiceInterface;
use App\Infrastructure\Caching\Invalidators\CategoryCacheInvalidator;
use App\Infrastructure\Caching\Invalidators\ProductCacheInvalidator;
use App\Infrastructure\Caching\KeyGenerator;
use App\Infrastructure\Caching\RedisCacheService;
use App\Infrastructure\Persistence\Eloquent\Category as CategoryModel;
use App\Infrastructure\Persistence\Eloquent\Product as ProductModel;
use App\Infrastructure\Persistence\Repositories\Cached\CachedCategoryRepository;
use App\Infrastructure\Persistence\Repositories\Cached\CachedProductRepository;
use App\Infrastructure\Persistence\Repositories\EloquentCategoryRepository;
use App\Infrastructure\Persistence\Repositories\EloquentContentRepository;
use App\Infrastructure\Persistence\Repositories\EloquentProductRepository;
use App\Infrastructure\Persistence\Repositories\EloquentPromptRepository;
use App\Infrastructure\Persistence\Repositories\EloquentSeoRepository;
use App\Infrastructure\Services\MirGazaProductParser;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CacheServiceInterface::class, RedisCacheService::class);
        $this->app->singleton(KeyGenerator::class);

        $this->app->bind(
            ProductRepositoryInterface::class,
            fn ($app) => config('caching.enabled')
                ? new CachedProductRepository(
                    $app->make(EloquentProductRepository::class),
                    $app->make(CacheServiceInterface::class),
                    $app->make(KeyGenerator::class)
                )
                : $app->make(EloquentProductRepository::class)
        );

        $this->app->bind(
            CategoryRepositoryInterface::class,
            fn ($app) => config('caching.enabled')
                ? new CachedCategoryRepository(
                    $app->make(EloquentCategoryRepository::class),
                    $app->make(CacheServiceInterface::class),
                    $app->make(KeyGenerator::class)
                )
                : $app->make(EloquentCategoryRepository::class)
        );

        $this->app->bind(
            ContentRepositoryInterface::class,
            EloquentContentRepository::class
        );

        $this->app->bind(
            SeoRepositoryInterface::class,
            EloquentSeoRepository::class
        );

        $this->app->bind(
            PromptRepositoryInterface::class,
            EloquentPromptRepository::class
        );

        $this->app->bind(
            ProductParserInterface::class,
            MirGazaProductParser::class
        );

        Number::useLocale('en');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        CategoryModel::observe(CategoryCacheInvalidator::class);
        ProductModel::observe(ProductCacheInvalidator::class);
    }
}
