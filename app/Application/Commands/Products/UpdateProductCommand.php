<?php

namespace App\Application\Commands\Products;

use App\Application\DTO\ProductDTO;

class UpdateProductCommand
{
    public function __construct(
        public int $id,
        public ProductDTO $dto
    ) {}
}
