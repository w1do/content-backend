<?php

namespace App\Application\Queries\Products;

class GetProductByIdQuery
{
    public function __construct(
        public int $id
    ) {}
}
