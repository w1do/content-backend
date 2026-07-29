<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Seo;

interface SeoRepositoryInterface
{
    public function findById(int $id): ?Seo;

    public function findBySeoable(string $type, int $id): ?Seo;

    public function save(Seo $seo): Seo;

    public function delete(int $id): bool;
}
