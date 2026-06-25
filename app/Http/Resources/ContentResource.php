<?php

namespace App\Http\Resources;

use App\Domain\Entities\Content;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ContentResource',
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
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class ContentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Content $this */
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'name' => $this->name,
            'slug' => $this->slug,
            'category_id' => $this->categoryId,
            'short_text' => $this->shortText,
            'full_text' => $this->fullText,
            'views' => $this->views,
            'tags' => $this->tags,
        ];
    }
}
