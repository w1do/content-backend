<?php

namespace App\Application\Commands\Categories;

use App\Application\DTO\CategoryDTO;

class UpdateCategoryCommand
{
    public function __construct(
        public int $id,
        public CategoryDTO $dto
    ) {}
}
