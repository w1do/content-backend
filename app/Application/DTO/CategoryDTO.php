<?php

namespace App\Application\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CategoryDTO',
    required: ['name', 'slug'],
    properties: [
        new OA\Property(property: 'parent_id', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Газовое оборудование'),
        new OA\Property(property: 'slug', type: 'string', example: 'gazovoe-oborudovanie'),
        new OA\Property(property: 'status', type: 'string', example: 'active'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Описание категории'),
    ]
)]
class CategoryDTO
{
    public function __construct(
        public ?int $parentId,
        public string $name,
        public string $slug,
        public string $status,
        public ?string $description = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            parentId: isset($data['parent_id']) || isset($data['parentId'])
                ? (int) ($data['parent_id'] ?? $data['parentId'])
                : null,
            name: (string) $data['name'],
            slug: (string) $data['slug'],
            status: (string) ($data['status'] ?? 'active'),
            description: $data['description'] ?? null,
        );
    }
}
