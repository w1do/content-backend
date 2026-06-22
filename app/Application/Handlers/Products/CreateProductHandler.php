<?php

namespace App\Application\Handlers\Products;

use App\Application\Commands\Products\CreateProductCommand;
use App\Domain\Entities\Product;
use App\Domain\Repositories\ProductRepositoryInterface;

class CreateProductHandler
{
    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}

    public function handle(CreateProductCommand $command): Product
    {
        $product = new Product(
            id: null,
            categoryId: $command->dto->categoryId,
            name: $command->dto->name,
            slug: $command->dto->slug,
            description: $command->dto->description,
            attributes: $command->dto->attributes,
        );

        return $this->repository->save($product);
    }
}
