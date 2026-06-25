<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\Product as ProductEntity;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Product as ProductModel;

class EloquentProductRepository implements ProductRepositoryInterface
{
    /**
     * @return ProductEntity[]
     */
    public function findAll(): array
    {
        return ProductModel::all()->map(fn (ProductModel $model) => $this->toEntity($model))->toArray();
    }

    public function findByCategories(array $categoryIds): array
    {
        return ProductModel::whereHas('categories', function ($query) use ($categoryIds) {
            $query->whereIn('categories.id', $categoryIds);
        })->get()->map(fn (ProductModel $model) => $this->toEntity($model))->toArray();
    }

    public function findById(int $id): ?ProductEntity
    {
        $model = ProductModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function save(ProductEntity $product): ProductEntity
    {
        $data = [
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'attributes' => $product->attributes,
        ];

        if ($product->id) {
            $model = ProductModel::findOrFail($product->id);
            $model->update($data);
        } else {
            $model = ProductModel::create($data);
        }

        // Handle category relationship if categoryId is provided (assuming it exists in database)
        // In the migration products table didn't have category_id, but categorizeable table exists
        if ($product->categoryId) {
            $model->categories()->sync([$product->categoryId]);
        }

        return $this->toEntity($model);
    }

    public function delete(int $id): bool
    {
        return (bool) ProductModel::destroy($id);
    }

    private function toEntity(ProductModel $model): ProductEntity
    {
        /** @var \App\Infrastructure\Persistence\Eloquent\Category|null $category */
        $category = $model->categories()->first();

        return new ProductEntity(
            id: $model->id,
            categoryId: $category ? $category->id : 0,
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            attributes: $model->attributes ?? [],
        );
    }
}
