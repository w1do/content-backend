<?php

namespace App\Domain\Services;

use App\Application\DTO\ImageSearchResult;

interface ImageSearchProviderInterface
{
    /**
     * Search for an image based on a query.
     */
    public function search(string $query): ?ImageSearchResult;
}
