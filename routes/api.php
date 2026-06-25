<?php

use App\Http\Controllers\Api\HealthCheckController;
use App\Presentation\Controllers\Api\V1\CategoryController;
use App\Presentation\Controllers\Api\V1\ContentController;
use App\Presentation\Controllers\Api\V1\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', HealthCheckController::class);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });

    // Products
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{id}', [ProductController::class, 'show']);
    });

    // Categories
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/tree', [CategoryController::class, 'tree']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::get('/{id}/breadcrumbs', [CategoryController::class, 'breadcrumbs']);
        Route::get('/{id}/products', [CategoryController::class, 'products']);
    });

    // Content
    Route::get('/blog', [ContentController::class, 'blog']);
    Route::get('/page', [ContentController::class, 'page']);
    Route::get('/system', [ContentController::class, 'system']);
    Route::get('/content', [ContentController::class, 'content']);
});
