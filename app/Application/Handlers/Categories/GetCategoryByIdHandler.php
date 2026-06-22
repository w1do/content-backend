<?php

namespace App\Application\Handlers\Categories;

use App\Application\Queries\Categories\GetCategoryByIdQuery;
use App\Domain\Entities\Category;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Repositories\CategoryRepositoryInterface;

class GetCategoryByIdHandler
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function handle(GetCategoryByIdQuery $query): Category
    {
        $category = $this->repository->findById($query->id);

        if (! $category) {
            throw new EntityNotFoundException('Category', (string) $query->id);
        }

        return $category;
    }
}
