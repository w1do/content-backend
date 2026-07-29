<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property string|null $title
 * @property string|null $description
 * @property bool $is_indexable
 * @property array|null $meta
 * @property string $seoable_type
 * @property int $seoable_id
 */
class Seo extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'seoable_type',
        'seoable_id',
        'title',
        'description',
        'is_indexable',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'is_indexable' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->useDisk('media')
            ->singleFile();
    }
}
