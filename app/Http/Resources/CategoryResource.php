<?php

namespace App\Http\Resources;

use App\Domain\Entities\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @mixin Category
 */
#[OA\Schema(
    schema: 'CategoryResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'parent_id', type: 'integer', nullable: true, example: null),
        new OA\Property(property: 'name', type: 'string', example: 'Газовое оборудование'),
        new OA\Property(property: 'slug', type: 'string', example: 'gazovoe-oborudovanie'),
        new OA\Property(property: 'status', type: 'string', example: 'active'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Описание категории'),
        new OA\Property(
            property: 'children',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/CategoryResource'),
            nullable: true
        ),
    ]
)]
class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parentId,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status,
            'description' => $this->description,
            'children' => $this->resource instanceof \Illuminate\Database\Eloquent\Model
                ? CategoryResource::collection($this->whenLoaded('children'))
                : null,
        ];
    }
}
