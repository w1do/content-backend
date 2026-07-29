<?php

namespace App\Application\Queries\Products;

use App\Application\DTO\ProductFilterDTO;

class GetProductsQuery
{
    public function __construct(
        public ?ProductFilterDTO $filters = null
    ) {}
}
