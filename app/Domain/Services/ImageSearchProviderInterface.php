<?php

namespace App\Domain\Services;

use App\Application\DTO\ImageSearchResult;

interface ImageSearchProviderInterface
{
    /**
     * Search for images based on a query.
     *
     * @return ImageSearchResult[]
     */
    public function search(string $query): array;
}
