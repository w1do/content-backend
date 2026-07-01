<?php

namespace App\Domain\Services;

use App\Application\DTO\ParsedProductDTO;

interface ProductParserInterface
{
    /**
     * Parse product data from the given URL.
     */
    public function parse(string $url): ParsedProductDTO;
}
