<?php

namespace App\Application\DTO;

use Spatie\LaravelData\Data;

class ImageSearchResult extends Data
{
    public function __construct(
        public string $url,
        public ?string $title = null,
        public ?string $source = null,
    ) {}
}
