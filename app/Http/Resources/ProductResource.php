<?php

namespace App\Http\Resources;

use App\Domain\Entities\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @mixin Product
 */
#[OA\Schema(
    schema: 'ProductResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'category_id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Газовый котел'),
        new OA\Property(property: 'slug', type: 'string', example: 'gazoviy-kotel'),
        new OA\Property(property: 'description', type: 'string', example: 'Описание товара', nullable: true),
        new OA\Property(property: 'attributes', type: 'object', example: ['power' => '24kW']),
    ]
)]
class ProductResource extends JsonResource
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
            'category_id' => $this->categoryId,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'attributes' => $this->attributes,
        ];
    }
}
