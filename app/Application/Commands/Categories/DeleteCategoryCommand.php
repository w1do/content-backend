<?php

namespace App\Application\Commands\Categories;

class DeleteCategoryCommand
{
    public function __construct(
        public int $id
    ) {}
}
