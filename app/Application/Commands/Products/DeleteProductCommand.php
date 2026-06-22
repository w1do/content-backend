<?php

namespace App\Application\Commands\Products;

class DeleteProductCommand
{
    public function __construct(
        public int $id
    ) {}
}
