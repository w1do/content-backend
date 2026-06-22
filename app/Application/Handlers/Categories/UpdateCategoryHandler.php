<?php

namespace App\Application\Handlers\Categories;

use App\Application\Commands\Categories\UpdateCategoryCommand;
use App\Domain\Entities\Category;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Repositories\CategoryRepositoryInterface;

class UpdateCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function handle(UpdateCategoryCommand $command): Category
    {
        $category = $this->repository->findById($command->id);

        if (! $category) {
            throw new EntityNotFoundException('Category', (string) $command->id);
        }

        $category->parentId = $command->dto->parentId;
        $category->name = $command->dto->name;
        $category->slug = $command->dto->slug;
        $category->status = $command->dto->status;
        $category->description = $command->dto->description;

        return $this->repository->save($category);
    }
}
