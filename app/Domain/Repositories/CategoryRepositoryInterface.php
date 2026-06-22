<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Category;

interface CategoryRepositoryInterface
{
    /**
     * @return Category[]
     */
    public function findAll(): array;

    /**
     * @return Category[]
     */
    public function findTree(): array;

    public function findById(int $id): ?Category;

    public function findBySlug(string $slug): ?Category;

    public function save(Category $category): Category;

    public function delete(int $id): bool;

    /**
     * @return Category[]
     */
    public function getAncestors(int $id): array;
}
