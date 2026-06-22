<?php

namespace App\Application\Commands\Products;

use App\Application\DTO\ProductDTO;

class CreateProductCommand
{
    public function __construct(
        public ProductDTO $dto
    ) {}
}
