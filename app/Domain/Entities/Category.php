<?php

namespace App\Domain\Entities;

class Category
{
    /**
     * @param  Category[]  $children
     */
    public function __construct(
        public readonly ?int $id,
        public ?int $parentId,
        public string $name,
        public string $slug,
        public string $status,
        public string $fullPath,
        public ?string $description = null,
        public array $children = [],
        public ?int $productsCount = null,
        public ?string $coverUrl = null,
    ) {}
}
