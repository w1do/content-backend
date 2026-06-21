<?php

namespace App\Domain\Entities;

class Category
{
    public function __construct(
        public readonly ?int $id,
        public ?int $parentId,
        public string $name,
        public string $slug,
        public string $status,
        public ?string $description = null,
    ) {}
}
