<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\Prompt as PromptEntity;
use App\Domain\Enums\PromptCategory;
use App\Domain\Repositories\PromptRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Prompt as PromptModel;

class EloquentPromptRepository implements PromptRepositoryInterface
{
    public function getActiveByCategory(PromptCategory $category): ?PromptEntity
    {
        $model = PromptModel::where('category', $category)
            ->where('status', true)
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    private function toEntity(PromptModel $model): PromptEntity
    {
        return new PromptEntity(
            id: $model->id,
            category: $model->category,
            rule: $model->rule,
            status: $model->status,
        );
    }
}
