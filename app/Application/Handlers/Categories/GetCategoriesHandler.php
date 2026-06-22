<?php

namespace App\Application\Handlers\Categories;

use App\Application\Queries\Categories\GetCategoriesQuery;
use App\Domain\Repositories\CategoryRepositoryInterface;

class GetCategoriesHandler
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function handle(GetCategoriesQuery $query): array
    {
        return $this->repository->findAll();
    }
}
