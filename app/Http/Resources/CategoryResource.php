<?php

namespace App\Http\Resources;

use App\Domain\Entities\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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
        new OA\Property(property: 'full_path', type: 'string', example: 'oborudovanie/gazovoe-oborudovanie'),
        new OA\Property(property: 'status', type: 'string', example: 'active'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Описание категории'),
        new OA\Property(
            property: 'children',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/CategoryResource'),
            nullable: true
        ),
        new OA\Property(property: 'products_count', type: 'integer', nullable: true, example: 5),
        new OA\Property(property: 'cover_url', type: 'string', example: 'http://localhost/storage/1/conversions/image-cover.jpg', nullable: true),
        new OA\Property(property: 'seo', ref: '#/components/schemas/SeoResource', nullable: true),
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
            'full_path' => $this->fullPath,
            'status' => $this->status,
            'description' => $this->description,
            'children' => $this->resolveChildren(),
            'products_count' => $this->when(isset($this->productsCount), $this->productsCount),
            'cover_url' => $this->coverUrl,
            'seo' => new SeoResource($this->seo),
        ];
    }

    /**
     * Resolve the `children` collection for both Eloquent models (via the loaded
     * relation) and Domain `Category` entities (which already carry their own
     * recursively populated `children` array).
     */
    private function resolveChildren(): ?AnonymousResourceCollection
    {
        if ($this->resource instanceof Model) {
            return CategoryResource::collection($this->whenLoaded('children'));
        }

        if ($this->resource instanceof Category) {
            return CategoryResource::collection($this->resource->children);
        }

        return null;
    }
}
