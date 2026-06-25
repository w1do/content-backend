<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\Content as ContentEntity;
use App\Domain\Enums\ContentType;
use App\Domain\Repositories\ContentRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Content as ContentModel;

class EloquentContentRepository implements ContentRepositoryInterface
{
    /**
     * @return ContentEntity[]
     */
    public function findAll(): array
    {
        return ContentModel::all()->map(fn (ContentModel $model) => $this->toEntity($model))->toArray();
    }

    /**
     * @return ContentEntity[]
     */
    public function findByType(ContentType $type): array
    {
        return ContentModel::where('type', $type)
            ->get()
            ->map(fn (ContentModel $model) => $this->toEntity($model))
            ->toArray();
    }

    public function findById(int $id): ?ContentEntity
    {
        $model = ContentModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findBySlug(string $slug): ?ContentEntity
    {
        $model = ContentModel::where('slug', $slug)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function save(ContentEntity $content): ContentEntity
    {
        $data = [
            'type' => $content->type->value,
            'name' => $content->name,
            'slug' => $content->slug,
            'category_id' => $content->categoryId,
            'short_text' => $content->shortText,
            'full_text' => $content->fullText,
            'views' => $content->views,
            'tags' => $content->tags,
        ];

        if ($content->id) {
            $model = ContentModel::findOrFail($content->id);
            $model->update($data);
        } else {
            $model = ContentModel::create($data);
        }

        return $this->toEntity($model);
    }

    public function delete(int $id): bool
    {
        return (bool) ContentModel::destroy($id);
    }

    private function toEntity(ContentModel $model): ContentEntity
    {
        return new ContentEntity(
            id: $model->id,
            type: $model->type,
            name: $model->name,
            slug: $model->slug,
            categoryId: $model->category_id,
            shortText: $model->short_text,
            fullText: $model->full_text,
            views: $model->views,
            tags: $model->tags ?? [],
        );
    }
}
