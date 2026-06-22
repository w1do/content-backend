<?php

namespace App\Application\Queries\Products;

class GetProductsByCategoryQuery
{
    public function __construct(
        public int $categoryId,
        public bool $includeChildren = true
    ) {}
}
