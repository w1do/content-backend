<?php

namespace App\Application\Handlers\Categories;

use App\Application\Commands\Categories\CreateCategoryCommand;
use App\Domain\Entities\Category;
use App\Domain\Repositories\CategoryRepositoryInterface;

class CreateCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function handle(CreateCategoryCommand $command): Category
    {
        $category = new Category(
            id: null,
            parentId: $command->dto->parentId,
            name: $command->dto->name,
            slug: $command->dto->slug,
            status: $command->dto->status,
            description: $command->dto->description,
        );

        return $this->repository->save($category);
    }
}
