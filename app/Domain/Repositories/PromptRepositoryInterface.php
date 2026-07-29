<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Prompt;
use App\Domain\Enums\PromptCategory;

interface PromptRepositoryInterface
{
    public function getActiveByCategory(PromptCategory $category): ?Prompt;
}
