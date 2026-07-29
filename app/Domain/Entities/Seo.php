<?php

namespace App\Domain\Entities;

class Seo
{
    public function __construct(
        public readonly ?int $id,
        public string $seoableType,
        public int $seoableId,
        public ?string $title = null,
        public ?string $description = null,
        public bool $isIndexable = true,
        public array $meta = [],
        public ?string $imageUrl = null,
    ) {}
}
