<?php

namespace App\Domain\Entities;

use App\Domain\Enums\ContentType;

class Content
{
    public function __construct(
        public readonly ?int $id,
        public ContentType $type,
        public string $name,
        public string $slug,
        public ?int $categoryId = null,
        /** @var string|null Краткий текст в формате Markdown */
        public ?string $shortText = null,
        /** @var string|null Полный текст в формате Markdown */
        public ?string $fullText = null,
        public int $views = 0,
        public array $tags = [],
    ) {}
}
