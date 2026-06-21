<?php

namespace App\Domain\Entities;

class Product
{
    public function __construct(
        public readonly ?int $id,
        public int $categoryId,
        public string $name,
        public string $slug,
        public ?string $description = null,
        public array $attributes = [],
    ) {}
}
