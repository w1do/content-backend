<?php

namespace App\Application\DTO;

use Spatie\LaravelData\Data;

class ParsedProductDTO extends Data
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public array $attributes = [],
    ) {}
}
