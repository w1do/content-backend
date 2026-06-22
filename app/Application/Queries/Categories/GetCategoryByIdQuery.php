<?php

namespace App\Application\Queries\Categories;

class GetCategoryByIdQuery
{
    public function __construct(
        public int $id
    ) {}
}
