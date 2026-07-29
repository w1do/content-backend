<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Application\DTO\ProductFilterDTO;
use App\Domain\Entities\Product as ProductEntity;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Category;
use App\Infrastructure\Persistence\Eloquent\Filters\Products\ProductCategoryFullPathFilter;
use App\Infrastructure\Persistence\Eloquent\Filters\Products\ProductCategoryIdFilter;
use App\Infrastructure\Persistence\Eloquent\Filters\Products\ProductCategoryNameFilter;
use App\Infrastructure\Persistence\Eloquent\Product as ProductModel;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EloquentProductRepository implements ProductRepositoryInterface
{
    /**
     * @return ProductEntity[]
     */
    public function findAll(?ProductFilterDTO $filters = null): array
    {
        $request = $filters
            ? new Request(['filter' => array_filter($filters->toArray())])
            : request();

        return QueryBuilder::for(ProductModel::class, $request)
            ->with('categories')
            ->allowedFilters(
                AllowedFilter::custom('category_id', new ProductCategoryIdFilter),
                AllowedFilter::custom('category_name', new ProductCategoryNameFilter),
                AllowedFilter::custom('category_full_path', new ProductCategoryFullPathFilter),
            )
            ->get()
            ->map(fn (ProductModel $model) => $this->toEntity($model))
            ->toArray();
    }

    public function findByCategories(array $categoryIds): array
    {
        return ProductModel::with('categories')->whereHas('categories', function ($query) use ($categoryIds) {
            $query->whereIn('categories.id', $categoryIds);
        })->get()->map(fn (ProductModel $model) => $this->toEntity($model))->toArray();
    }

    public function findById(int $id): ?ProductEntity
    {
        $model = ProductModel::with('categories')->find($id);

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
        /** @var Category|null $category */
        $category = $model->relationLoaded('categories')
            ? $model->categories->first()
            : $model->categories()->first();

        return new ProductEntity(
            id: $model->id,
            categoryId: $category ? $category->id : 0,
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            attributes: $model->attributes ?? [],
            categoryFullPath: $category?->full_path,
            coverUrl: $model->getFirstMediaUrl('cover', 'cover') ?: $model->getFirstMediaUrl('main', 'cover'),
        );
    }
}
