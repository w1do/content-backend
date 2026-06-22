<?php

declare(strict_types=1);

namespace App\Presentation\Controllers\Api\V1;

use App\Application\Commands\Products\CreateProductCommand;
use App\Application\Commands\Products\DeleteProductCommand;
use App\Application\Commands\Products\UpdateProductCommand;
use App\Application\DTO\ProductDTO;
use App\Application\Handlers\Products\CreateProductHandler;
use App\Application\Handlers\Products\DeleteProductHandler;
use App\Application\Handlers\Products\GetProductByIdHandler;
use App\Application\Handlers\Products\GetProductsHandler;
use App\Application\Handlers\Products\UpdateProductHandler;
use App\Application\Queries\Products\GetProductByIdQuery;
use App\Application\Queries\Products\GetProductsQuery;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Presentation\Requests\Products\StoreProductRequest;
use App\Presentation\Requests\Products\UpdateProductRequest;
use Illuminate\Http\JsonResponse;
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

    #[OA\Post(
        path: '/api/v1/products',
        summary: 'Создать новый товар',
        tags: ['Products'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ProductDTO')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Товар успешно создан',
                content: new OA\JsonContent(ref: '#/components/schemas/ProductResource')
            ),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ]
    )]
    public function store(StoreProductRequest $request, CreateProductHandler $handler): JsonResponse
    {
        $dto = ProductDTO::fromArray($request->validated());
        $product = $handler->handle(new CreateProductCommand($dto));

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Put(
        path: '/api/v1/products/{id}',
        summary: 'Обновить существующий товар',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ProductDTO')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Товар успешно обновлен',
                content: new OA\JsonContent(ref: '#/components/schemas/ProductResource')
            ),
            new OA\Response(response: 404, description: 'Товар не найден'),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ]
    )]
    public function update(UpdateProductRequest $request, string $id, UpdateProductHandler $handler): ProductResource
    {
        $dto = ProductDTO::fromArray($request->validated());
        $product = $handler->handle(new UpdateProductCommand((int) $id, $dto));

        return new ProductResource($product);
    }

    #[OA\Delete(
        path: '/api/v1/products/{id}',
        summary: 'Удалить товар',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Товар успешно удален'),
            new OA\Response(response: 404, description: 'Товар не найден'),
        ]
    )]
    public function destroy(string $id, DeleteProductHandler $handler): JsonResponse
    {
        $handler->handle(new DeleteProductCommand((int) $id));

        return new JsonResponse(null, 204);
    }
}
