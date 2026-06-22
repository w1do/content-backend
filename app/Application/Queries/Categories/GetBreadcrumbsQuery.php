<?php

namespace App\Application\Queries\Categories;

class GetBreadcrumbsQuery
{
    public function __construct(
        public int $categoryId
    ) {}
}
