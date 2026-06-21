<?php

declare(strict_types=1);

namespace App\Presentation\Controllers\Api\V1;

use App\Domain\Exceptions\EntityNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ProductResource::collection([]);
    }

    public function show(string $id): ProductResource
    {
        if ($id === 'fail-domain') {
            throw new EntityNotFoundException('Product', $id);
        }

        // For now, return a dummy resource. Later this will be fetched from repository.
        return new ProductResource((object) ['id' => $id, 'categoryId' => 1, 'name' => 'Dummy', 'slug' => 'dummy', 'description' => null, 'attributes' => []]);
    }
}
