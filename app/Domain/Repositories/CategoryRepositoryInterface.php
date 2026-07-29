<?php

namespace App\Domain\Repositories;

use App\Application\DTO\CategoryFilterDTO;
use App\Domain\Entities\Category;

interface CategoryRepositoryInterface
{
    /**
     * @param  string[]  $includes
     * @return Category[]
     */
    public function findAll(?CategoryFilterDTO $filters = null, array $includes = []): array;

    /**
     * @return Category[]
     */
    public function findTree(): array;

    /**
     * @param  string[]  $includes
     */
    public function findById(int $id, array $includes = []): ?Category;

    public function findBySlug(string $slug): ?Category;

    public function save(Category $category): Category;

    public function delete(int $id): bool;

    /**
     * @return Category[]
     */
    public function getAncestors(int $id): array;
}
