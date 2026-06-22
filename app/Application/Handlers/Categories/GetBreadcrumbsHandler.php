<?php

namespace App\Application\Handlers\Categories;

use App\Application\Queries\Categories\GetBreadcrumbsQuery;
use App\Domain\Repositories\CategoryRepositoryInterface;

class GetBreadcrumbsHandler
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function handle(GetBreadcrumbsQuery $query): array
    {
        return $this->repository->getAncestors($query->categoryId);
    }
}
