<?php

declare(strict_types=1);

namespace App\Presentation\Controllers\Api\V1;

use App\Application\Handlers\Content\GetContentByTypeHandler;
use App\Application\Queries\Content\GetContentByTypeQuery;
use App\Domain\Enums\ContentType;
use App\Http\Controllers\Controller;
use App\Http\Resources\ContentResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Content', description: 'Управление контентом')]
final class ContentController extends Controller
{
    #[OA\Get(
        path: '/api/v1/blog',
        summary: 'Получить список постов блога',
        tags: ['Content'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список постов',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/ContentResource')
                )
            ),
        ]
    )]
    public function blog(GetContentByTypeHandler $handler): AnonymousResourceCollection
    {
        $contents = $handler->handle(new GetContentByTypeQuery(ContentType::Blog));

        return ContentResource::collection($contents);
    }

    #[OA\Get(
        path: '/api/v1/page',
        summary: 'Получить список страниц',
        tags: ['Content'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список страниц',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/ContentResource')
                )
            ),
        ]
    )]
    public function page(GetContentByTypeHandler $handler): AnonymousResourceCollection
    {
        $contents = $handler->handle(new GetContentByTypeQuery(ContentType::Page));

        return ContentResource::collection($contents);
    }

    #[OA\Get(
        path: '/api/v1/system',
        summary: 'Получить список системных страниц',
        tags: ['Content'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список системных страниц',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/ContentResource')
                )
            ),
        ]
    )]
    public function system(GetContentByTypeHandler $handler): AnonymousResourceCollection
    {
        $contents = $handler->handle(new GetContentByTypeQuery(ContentType::System));

        return ContentResource::collection($contents);
    }

    #[OA\Get(
        path: '/api/v1/content',
        summary: 'Получить список материалов',
        tags: ['Content'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список материалов',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/ContentResource')
                )
            ),
        ]
    )]
    public function content(GetContentByTypeHandler $handler): AnonymousResourceCollection
    {
        $contents = $handler->handle(new GetContentByTypeQuery(ContentType::Material));

        return ContentResource::collection($contents);
    }
}
