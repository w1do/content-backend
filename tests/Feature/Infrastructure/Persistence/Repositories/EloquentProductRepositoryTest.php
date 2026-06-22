<?php

use App\Domain\Entities\Product as ProductEntity;
use App\Infrastructure\Persistence\Eloquent\Category;
use App\Infrastructure\Persistence\Eloquent\Product as ProductModel;
use App\Infrastructure\Persistence\Repositories\EloquentProductRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it can find all products', function () {
    $category = Category::factory()->create();
    $models = ProductModel::factory()->count(3)->create();
    foreach ($models as $model) {
        $model->categories()->attach($category);
    }

    $repository = new EloquentProductRepository;
    $products = $repository->findAll();

    expect($products)->toHaveCount(3)
        ->and($products[0])->toBeInstanceOf(ProductEntity::class);
});

test('it can find a product by id', function () {
    $category = Category::factory()->create();
    $model = ProductModel::factory()->create();
    $model->categories()->attach($category);

    $repository = new EloquentProductRepository;
    $product = $repository->findById($model->id);

    expect($product)->toBeInstanceOf(ProductEntity::class)
        ->and($product->id)->toBe($model->id)
        ->and($product->name)->toBe($model->name);
});

test('it can save a new product', function () {
    $category = Category::factory()->create();
    $entity = new ProductEntity(
        id: null,
        categoryId: $category->id,
        name: 'New Product',
        slug: 'new-product',
        description: 'Description',
        attributes: ['key' => 'value']
    );

    $repository = new EloquentProductRepository;
    $savedProduct = $repository->save($entity);

    expect($savedProduct->id)->not->toBeNull()
        ->and($savedProduct->name)->toBe('New Product');

    $this->assertDatabaseHas('products', ['name' => 'New Product']);
});

test('it can update an existing product', function () {
    $category = Category::factory()->create();
    $model = ProductModel::factory()->create();
    $model->categories()->attach($category);

    $entity = new ProductEntity(
        id: $model->id,
        categoryId: $category->id,
        name: 'Updated Name',
        slug: 'updated-slug',
        description: 'Updated Description',
        attributes: ['key' => 'updated']
    );

    $repository = new EloquentProductRepository;
    $updatedProduct = $repository->save($entity);

    expect($updatedProduct->name)->toBe('Updated Name');
    $this->assertDatabaseHas('products', ['id' => $model->id, 'name' => 'Updated Name']);
});

test('it can delete a product', function () {
    $model = ProductModel::factory()->create();

    $repository = new EloquentProductRepository;
    $result = $repository->delete($model->id);

    expect($result)->toBeTrue();
    $this->assertDatabaseMissing('products', ['id' => $model->id]);
});
