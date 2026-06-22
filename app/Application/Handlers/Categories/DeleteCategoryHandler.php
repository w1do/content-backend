<?php

namespace App\Application\Handlers\Categories;

use App\Application\Commands\Categories\DeleteCategoryCommand;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Repositories\CategoryRepositoryInterface;

class DeleteCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function handle(DeleteCategoryCommand $command): void
    {
        $category = $this->repository->findById($command->id);

        if (! $category) {
            throw new EntityNotFoundException('Category', (string) $command->id);
        }

        $this->repository->delete($command->id);
    }
}
