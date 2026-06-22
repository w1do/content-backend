<?php

namespace App\Application\Handlers\Products;

use App\Application\Queries\Products\GetProductsQuery;
use App\Domain\Repositories\ProductRepositoryInterface;

class GetProductsHandler
{
    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}

    public function handle(GetProductsQuery $query): array
    {
        return $this->repository->findAll();
    }
}
