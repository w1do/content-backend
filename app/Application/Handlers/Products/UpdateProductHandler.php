<?php

namespace App\Application\Handlers\Products;

use App\Application\Commands\Products\UpdateProductCommand;
use App\Domain\Entities\Product;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Repositories\ProductRepositoryInterface;

class UpdateProductHandler
{
    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}

    public function handle(UpdateProductCommand $command): Product
    {
        $product = $this->repository->findById($command->id);

        if (! $product) {
            throw new EntityNotFoundException('Product', (string) $command->id);
        }

        $product->categoryId = $command->dto->categoryId;
        $product->name = $command->dto->name;
        $product->slug = $command->dto->slug;
        $product->description = $command->dto->description;
        $product->attributes = $command->dto->attributes;

        return $this->repository->save($product);
    }
}
