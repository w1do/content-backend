<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\Seo as SeoEntity;
use App\Domain\Repositories\SeoRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Seo as SeoModel;

class EloquentSeoRepository implements SeoRepositoryInterface
{
    public function findById(int $id): ?SeoEntity
    {
        $model = SeoModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findBySeoable(string $type, int $id): ?SeoEntity
    {
        $model = SeoModel::where('seoable_type', $type)
            ->where('seoable_id', $id)
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function save(SeoEntity $seo): SeoEntity
    {
        $data = [
            'seoable_type' => $seo->seoableType,
            'seoable_id' => $seo->seoableId,
            'title' => $seo->title,
            'description' => $seo->description,
            'is_indexable' => $seo->isIndexable,
            'meta' => $seo->meta,
        ];

        if ($seo->id) {
            $model = SeoModel::findOrFail($seo->id);
            $model->update($data);
        } else {
            $model = SeoModel::create($data);
        }

        return $this->toEntity($model);
    }

    public function delete(int $id): bool
    {
        return (bool) SeoModel::destroy($id);
    }

    private function toEntity(SeoModel $model): SeoEntity
    {
        return new SeoEntity(
            id: $model->id,
            seoableType: $model->seoable_type,
            seoableId: $model->seoable_id,
            title: $model->title,
            description: $model->description,
            isIndexable: $model->is_indexable,
            meta: $model->meta ?? [],
            imageUrl: $model->getFirstMediaUrl('image'),
        );
    }
}
