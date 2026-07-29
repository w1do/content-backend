<?php

namespace App\Domain\Entities;

use App\Domain\Enums\PromptCategory;

class Prompt
{
    public function __construct(
        public readonly ?int $id,
        public PromptCategory $category,
        public string $rule,
        public bool $status = true,
    ) {}
}
