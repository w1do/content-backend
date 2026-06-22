<?php

namespace App\Application\Handlers\Products;

use App\Application\Queries\Products\GetProductByIdQuery;
use App\Domain\Entities\Product;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Repositories\ProductRepositoryInterface;

class GetProductByIdHandler
{
    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}

    public function handle(GetProductByIdQuery $query): Product
    {
        $product = $this->repository->findById($query->id);

        if (! $product) {
            throw new EntityNotFoundException('Product', (string) $query->id);
        }

        return $product;
    }
}
