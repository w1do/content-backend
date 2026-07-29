<?php

namespace App\Domain\Repositories;

use App\Application\DTO\ProductFilterDTO;
use App\Domain\Entities\Product;

interface ProductRepositoryInterface
{
    /**
     * @return Product[]
     */
    public function findAll(?ProductFilterDTO $filters = null): array;

    /**
     * @param  int[]  $categoryIds
     * @return Product[]
     */
    public function findByCategories(array $categoryIds): array;

    public function findById(int $id): ?Product;

    public function save(Product $product): Product;

    public function delete(int $id): bool;
}
