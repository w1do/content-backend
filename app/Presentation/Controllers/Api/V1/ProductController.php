<?php

declare(strict_types=1);

namespace App\Presentation\Controllers\Api\V1;

use App\Application\Handlers\Products\GetProductByIdHandler;
use App\Application\Handlers\Products\GetProductsHandler;
use App\Application\Queries\Products\GetProductByIdQuery;
use App\Application\Queries\Products\GetProductsQuery;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Products', description: 'Управление товарами')]
final class ProductController extends Controller
{
    #[OA\Get(
        path: '/api/v1/products',
        summary: 'Получить список всех товаров',
        tags: ['Products'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список товаров',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/ProductResource')
                )
            ),
        ]
    )]
    public function index(GetProductsHandler $handler): AnonymousResourceCollection
    {
        $products = $handler->handle(new GetProductsQuery);

        return ProductResource::collection($products);
    }

    #[OA\Get(
        path: '/api/v1/products/{id}',
        summary: 'Получить информацию о товаре по ID',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Информация о товаре',
                content: new OA\JsonContent(ref: '#/components/schemas/ProductResource')
            ),
            new OA\Response(response: 404, description: 'Товар не найден'),
        ]
    )]
    public function show(string $id, GetProductByIdHandler $handler): ProductResource
    {
        $product = $handler->handle(new GetProductByIdQuery((int) $id));

        return new ProductResource($product);
    }
}
