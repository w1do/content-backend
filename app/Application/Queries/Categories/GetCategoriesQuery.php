<?php

namespace App\Application\Queries\Categories;

use App\Application\DTO\CategoryFilterDTO;

class GetCategoriesQuery
{
    public function __construct(
        public ?CategoryFilterDTO $filters = null
    ) {}
}
