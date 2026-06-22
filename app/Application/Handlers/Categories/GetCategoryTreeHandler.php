<?php

namespace App\Application\Handlers\Categories;

use App\Application\Queries\Categories\GetCategoryTreeQuery;
use App\Domain\Repositories\CategoryRepositoryInterface;

class GetCategoryTreeHandler
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function handle(GetCategoryTreeQuery $query): array
    {
        return $this->repository->findTree();
    }
}
