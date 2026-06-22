<?php

namespace App\Application\Handlers\Products;

use App\Application\Commands\Products\DeleteProductCommand;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Repositories\ProductRepositoryInterface;

class DeleteProductHandler
{
    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}

    public function handle(DeleteProductCommand $command): void
    {
        $product = $this->repository->findById($command->id);

        if (! $product) {
            throw new EntityNotFoundException('Product', (string) $command->id);
        }

        $this->repository->delete($command->id);
    }
}
