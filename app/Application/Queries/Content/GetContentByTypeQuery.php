<?php

namespace App\Application\Queries\Content;

use App\Domain\Enums\ContentType;

class GetContentByTypeQuery
{
    public function __construct(
        public readonly ContentType $type
    ) {}
}
