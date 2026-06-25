<?php

namespace App\Application\Handlers\Products;

use App\Application\Queries\Products\GetProductsByCategoryQuery;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Category as CategoryModel;

class GetProductsByCategoryHandler
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function handle(GetProductsByCategoryQuery $query): array
    {
        $categoryIds = [$query->categoryId];

        if ($query->includeChildren) {
            // We use Eloquent model directly here for performance and Nested Set capabilities
            // but in a strict DDD we might want to do it through repository
            $categoryModel = CategoryModel::find($query->categoryId);
            if ($categoryModel) {
                $childrenIds = $categoryModel->descendants()->getQuery()->pluck('id')->toArray();
                $categoryIds = array_merge($categoryIds, $childrenIds);
            }
        }

        return $this->productRepository->findByCategories($categoryIds);
    }
}
