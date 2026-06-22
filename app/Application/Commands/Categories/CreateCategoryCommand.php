<?php

namespace App\Application\Commands\Categories;

use App\Application\DTO\CategoryDTO;

class CreateCategoryCommand
{
    public function __construct(
        public CategoryDTO $dto
    ) {}
}
