<?php

namespace App\Http\Resources;

use App\Domain\Entities\Seo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @mixin Seo
 */
#[OA\Schema(
    schema: 'SeoResource',
    properties: [
        new OA\Property(property: 'title', type: 'string', example: 'Заголовок страницы', nullable: true),
        new OA\Property(property: 'description', type: 'string', example: 'Описание страницы для поисковиков', nullable: true),
        new OA\Property(property: 'is_indexable', type: 'boolean', example: true),
        new OA\Property(property: 'image_url', type: 'string', example: 'http://localhost/storage/1/seo/image.jpg', nullable: true),
        new OA\Property(property: 'meta', type: 'object', example: ['keywords' => 'газ, котлы']),
    ]
)]
class SeoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'is_indexable' => $this->isIndexable,
            'image_url' => $this->imageUrl,
            'meta' => $this->meta,
        ];
    }
}
