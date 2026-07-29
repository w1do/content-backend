<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Application\DTO\CategoryFilterDTO;
use App\Domain\Entities\Category as CategoryEntity;
use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Category as CategoryModel;
use App\Infrastructure\Persistence\Eloquent\Filters\Categories\CategoryFullPathFilter;
use App\Infrastructure\Persistence\Eloquent\Filters\Categories\CategoryIdFilter;
use App\Infrastructure\Persistence\Eloquent\Filters\Categories\CategoryNameFilter;
use App\Infrastructure\Persistence\Eloquent\Filters\Categories\CategorySlugFilter;
use Illuminate\Http\Request;
use Kalnoy\Nestedset\Collection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @param  string[]  $includes
     * @return CategoryEntity[]
     */
    public function findAll(?CategoryFilterDTO $filters = null, array $includes = []): array
    {
        $params = [];
        if ($filters) {
            $params['filter'] = array_filter($filters->toArray());
        }
        if (! empty($includes)) {
            $params['include'] = implode(',', $includes);
        }

        $request = new Request($params);

        $query = QueryBuilder::for(CategoryModel::class, $request)
            ->with('children')
            ->allowedFilters(
                AllowedFilter::custom('id', new CategoryIdFilter),
                AllowedFilter::custom('name', new CategoryNameFilter),
                AllowedFilter::custom('slug', new CategorySlugFilter),
                AllowedFilter::custom('full_path', new CategoryFullPathFilter),
            )
            ->allowedIncludes(
                AllowedInclude::count('products', 'products'),
            );

        $results = $query->defaultOrder()->get();

        return $results
            ->map(fn (CategoryModel $model) => $this->toEntity($model))
            ->toArray();
    }

    /**
     * @return CategoryEntity[]
     */
    public function findTree(): array
    {
        // For simple tree representation in entities, we might need a more complex toEntity
        // but for now, let's just return a flat list ordered as a tree
        /** @var Collection $collection */
        $collection = CategoryModel::defaultOrder()->get();

        return $collection->toTree()
            ->map(fn (CategoryModel $model) => $this->toEntity($model))
            ->toArray();
    }

    /**
     * @param  string[]  $includes
     */
    public function findById(int $id, array $includes = []): ?CategoryEntity
    {
        $params = [];
        if (! empty($includes)) {
            $params['include'] = implode(',', $includes);
        }

        $request = new Request($params);

        /** @var CategoryModel|null $model */
        $model = QueryBuilder::for(CategoryModel::class, $request)
            ->with('children')
            ->allowedIncludes(
                AllowedInclude::count('products', 'products'),
            )
            ->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findBySlug(string $slug): ?CategoryEntity
    {
        $model = CategoryModel::with('children')->where('slug', $slug)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function save(CategoryEntity $category): CategoryEntity
    {
        $data = [
            'parent_id' => $category->parentId,
            'name' => $category->name,
            'slug' => $category->slug,
            'status' => $category->status,
            'description' => $category->description,
        ];

        if ($category->id) {
            $model = CategoryModel::findOrFail($category->id);
            $model->update($data);
        } else {
            $model = CategoryModel::create($data);
        }

        return $this->toEntity($model);
    }

    public function delete(int $id): bool
    {
        $model = CategoryModel::find($id);
        if ($model) {
            return $model->delete();
        }

        return false;
    }

    /**
     * @return CategoryEntity[]
     */
    public function getAncestors(int $id): array
    {
        $model = CategoryModel::find($id);
        if (! $model) {
            return [];
        }

        return $model->ancestors()
            ->get()
            ->map(fn (CategoryModel $m) => $this->toEntity($m))
            ->toArray();
    }

    private function toEntity(CategoryModel $model): CategoryEntity
    {
        return new CategoryEntity(
            id: $model->id,
            parentId: $model->parent_id,
            name: $model->name,
            slug: $model->slug,
            status: $model->status,
            fullPath: $model->full_path ?? '',
            description: $model->description,
            children: $model->relationLoaded('children')
                ? $model->children->map(fn (CategoryModel $child) => $this->toEntity($child))->toArray()
                : [],
            productsCount: isset($model->products_count) ? (int) $model->products_count : (isset($model->products) && is_numeric($model->products) ? (int) $model->products : null),
            coverUrl: $model->getFirstMediaUrl('cover', 'cover') ?: $model->getFirstMediaUrl('main', 'cover'),
        );
    }
}
