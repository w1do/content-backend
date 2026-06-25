<?php

namespace App\Application\DTO;

use App\Domain\Entities\Content;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ContentDTO',
    required: ['type', 'name', 'slug'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'type', type: 'string', example: 'blog'),
        new OA\Property(property: 'name', type: 'string', example: 'Заголовок контента'),
        new OA\Property(property: 'slug', type: 'string', example: 'zagolovok-kontenta'),
        new OA\Property(property: 'category_id', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'short_text', type: 'string', nullable: true, example: 'Краткий текст'),
        new OA\Property(property: 'full_text', type: 'string', nullable: true, example: 'Полный текст контента'),
        new OA\Property(property: 'views', type: 'integer', example: 100),
        new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'string'), example: ['tag1', 'tag2']),
    ]
)]
class ContentDTO
{
    public function __construct(
        public ?int $id,
        public string $type,
        public string $name,
        public string $slug,
        public ?int $categoryId = null,
        public ?string $shortText = null,
        public ?string $fullText = null,
        public int $views = 0,
        public array $tags = [],
    ) {}

    public static function fromEntity(Content $content): self
    {
        return new self(
            id: $content->id,
            type: $content->type->value,
            name: $content->name,
            slug: $content->slug,
            categoryId: $content->categoryId,
            shortText: $content->shortText,
            fullText: $content->fullText,
            views: $content->views,
            tags: $content->tags,
        );
    }
}
