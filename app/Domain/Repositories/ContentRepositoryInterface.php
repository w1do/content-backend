<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Content;
use App\Domain\Enums\ContentType;

interface ContentRepositoryInterface
{
    /**
     * @return Content[]
     */
    public function findAll(): array;

    /**
     * @return Content[]
     */
    public function findByType(ContentType $type): array;

    public function findById(int $id): ?Content;

    public function findBySlug(string $slug): ?Content;

    public function save(Content $content): Content;

    public function delete(int $id): bool;
}
