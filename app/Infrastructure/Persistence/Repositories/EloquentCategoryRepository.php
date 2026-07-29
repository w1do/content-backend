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
use Spatie\QueryBuilder\QueryBuilder;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @return CategoryEntity[]
     */
    public function findAll(?CategoryFilterDTO $filters = null): array
    {
        $request = $filters
            ? new Request(['filter' => array_filter($filters->toArray())])
            : request();

        return QueryBuilder::for(CategoryModel::class, $request)
            ->with('children')
            ->allowedFilters(
                AllowedFilter::custom('id', new CategoryIdFilter),
                AllowedFilter::custom('name', new CategoryNameFilter),
                AllowedFilter::custom('slug', new CategorySlugFilter),
                AllowedFilter::custom('full_path', new CategoryFullPathFilter),
            )
            ->defaultOrder()
            ->get()
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

    public function findById(int $id): ?CategoryEntity
    {
        $model = CategoryModel::with('children')->find($id);

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
        );
    }
}
