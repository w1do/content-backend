<?php

declare(strict_types=1);

namespace App\Presentation\Controllers\Api\V1;

use App\Application\Commands\Categories\CreateCategoryCommand;
use App\Application\Commands\Categories\DeleteCategoryCommand;
use App\Application\Commands\Categories\UpdateCategoryCommand;
use App\Application\DTO\CategoryDTO;
use App\Application\Handlers\Categories\CreateCategoryHandler;
use App\Application\Handlers\Categories\DeleteCategoryHandler;
use App\Application\Handlers\Categories\GetBreadcrumbsHandler;
use App\Application\Handlers\Categories\GetCategoriesHandler;
use App\Application\Handlers\Categories\GetCategoryByIdHandler;
use App\Application\Handlers\Categories\GetCategoryTreeHandler;
use App\Application\Handlers\Categories\UpdateCategoryHandler;
use App\Application\Handlers\Products\GetProductsByCategoryHandler;
use App\Application\Queries\Categories\GetBreadcrumbsQuery;
use App\Application\Queries\Categories\GetCategoriesQuery;
use App\Application\Queries\Categories\GetCategoryByIdQuery;
use App\Application\Queries\Categories\GetCategoryTreeQuery;
use App\Application\Queries\Products\GetProductsByCategoryQuery;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Presentation\Requests\Categories\StoreCategoryRequest;
use App\Presentation\Requests\Categories\UpdateCategoryRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Categories', description: 'Управление категориями')]
final class CategoryController extends Controller
{
    #[OA\Get(
        path: '/api/v1/categories',
        summary: 'Получить список всех категорий',
        tags: ['Categories'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список категорий',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/CategoryResource')
                )
            ),
        ]
    )]
    public function index(GetCategoriesHandler $handler): AnonymousResourceCollection
    {
        $categories = $handler->handle(new GetCategoriesQuery);

        return CategoryResource::collection($categories);
    }

    #[OA\Get(
        path: '/api/v1/categories/tree',
        summary: 'Получить дерево категорий',
        tags: ['Categories'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Дерево категорий',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/CategoryResource')
                )
            ),
        ]
    )]
    public function tree(GetCategoryTreeHandler $handler): AnonymousResourceCollection
    {
        $categories = $handler->handle(new GetCategoryTreeQuery);

        return CategoryResource::collection($categories);
    }

    #[OA\Get(
        path: '/api/v1/categories/{id}',
        summary: 'Получить информацию о категории по ID',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Информация о категории',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryResource')
            ),
            new OA\Response(response: 404, description: 'Категория не найдена'),
        ]
    )]
    public function show(string $id, GetCategoryByIdHandler $handler): CategoryResource
    {
        $category = $handler->handle(new GetCategoryByIdQuery((int) $id));

        return new CategoryResource($category);
    }

    #[OA\Get(
        path: '/api/v1/categories/{id}/breadcrumbs',
        summary: 'Получить хлебные крошки для категории',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Хлебные крошки',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/CategoryResource')
                )
            ),
            new OA\Response(response: 404, description: 'Категория не найдена'),
        ]
    )]
    public function breadcrumbs(string $id, GetBreadcrumbsHandler $handler): AnonymousResourceCollection
    {
        $breadcrumbs = $handler->handle(new GetBreadcrumbsQuery((int) $id));

        return CategoryResource::collection($breadcrumbs);
    }

    #[OA\Get(
        path: '/api/v1/categories/{id}/products',
        summary: 'Получить товары категории (включая подкатегории)',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'include_children', in: 'query', required: false, schema: new OA\Schema(type: 'boolean', default: true)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список товаров',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/ProductResource')
                )
            ),
            new OA\Response(response: 404, description: 'Категория не найдена'),
        ]
    )]
    public function products(Request $request, string $id, GetProductsByCategoryHandler $handler): AnonymousResourceCollection
    {
        $includeChildren = $request->query('include_children', 'true') === 'true';
        $products = $handler->handle(new GetProductsByCategoryQuery((int) $id, $includeChildren));

        return ProductResource::collection($products);
    }

    #[OA\Post(
        path: '/api/v1/categories',
        summary: 'Создать новую категорию',
        tags: ['Categories'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CategoryDTO')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Категория успешно создана',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryResource')
            ),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ]
    )]
    public function store(StoreCategoryRequest $request, CreateCategoryHandler $handler): JsonResponse
    {
        $dto = CategoryDTO::fromArray($request->validated());
        $category = $handler->handle(new CreateCategoryCommand($dto));

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Put(
        path: '/api/v1/categories/{id}',
        summary: 'Обновить существующую категорию',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CategoryDTO')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Категория успешно обновлена',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryResource')
            ),
            new OA\Response(response: 404, description: 'Категория не найдена'),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ]
    )]
    public function update(UpdateCategoryRequest $request, string $id, UpdateCategoryHandler $handler): CategoryResource
    {
        $dto = CategoryDTO::fromArray($request->validated());
        $category = $handler->handle(new UpdateCategoryCommand((int) $id, $dto));

        return new CategoryResource($category);
    }

    #[OA\Delete(
        path: '/api/v1/categories/{id}',
        summary: 'Удалить категорию',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Категория успешно удалена'),
            new OA\Response(response: 404, description: 'Категория не найдена'),
        ]
    )]
    public function destroy(string $id, DeleteCategoryHandler $handler): JsonResponse
    {
        $handler->handle(new DeleteCategoryCommand((int) $id));

        return new JsonResponse(null, 204);
    }
}
