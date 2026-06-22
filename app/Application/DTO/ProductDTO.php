<?php

namespace App\Application\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProductDTO',
    required: ['category_id', 'name', 'slug'],
    properties: [
        new OA\Property(property: 'category_id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Газовый котел'),
        new OA\Property(property: 'slug', type: 'string', example: 'gazoviy-kotel'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Описание товара'),
        new OA\Property(property: 'attributes', type: 'object', nullable: true, example: ['power' => '24kW']),
    ]
)]
class ProductDTO
{
    public function __construct(
        public int $categoryId,
        public string $name,
        public string $slug,
        public ?string $description = null,
        public array $attributes = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            categoryId: (int) ($data['category_id'] ?? $data['categoryId']),
            name: (string) $data['name'],
            slug: (string) $data['slug'],
            description: $data['description'] ?? null,
            attributes: $data['attributes'] ?? [],
        );
    }
}
