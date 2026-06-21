<?php

declare(strict_types=1);

namespace App\Presentation\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CategoryResource::collection([]);
    }

    public function show(string $id): CategoryResource
    {
        // For now, return a dummy resource. Later this will be fetched from repository.
        return new CategoryResource((object) ['id' => $id, 'parentId' => null, 'name' => 'Dummy Category', 'slug' => 'dummy-cat', 'status' => 'active', 'description' => null]);
    }
}
