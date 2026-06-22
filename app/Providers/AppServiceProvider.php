<?php

namespace App\Providers;

use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\EloquentCategoryRepository;
use App\Infrastructure\Persistence\Repositories\EloquentProductRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            ProductRepositoryInterface::class,
            EloquentProductRepository::class
        );

        $this->app->bind(
            CategoryRepositoryInterface::class,
            EloquentCategoryRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
