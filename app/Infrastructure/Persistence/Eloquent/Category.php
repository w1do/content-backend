<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Kalnoy\Nestedset\NodeTrait;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $status
 * @property string|null $description
 * @property int|null $parent_id
 * @property int|null $depth
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Kalnoy\Nestedset\QueryBuilder query()
 * @method static \Kalnoy\Nestedset\QueryBuilder withDepth()
 * @method static \Kalnoy\Nestedset\QueryBuilder defaultOrder()
 * @method static \Kalnoy\Nestedset\QueryBuilder whereNotDescendantOf($model)
 * @method static void fixTree()
 *
 * @method \Kalnoy\Nestedset\Collection toTree()
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Category extends Model implements HasMedia
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    use HasSlug;
    use InteractsWithMedia;
    use NodeTrait {
        NodeTrait::replicate as replicateNode;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('main')
            ->useDisk('media')
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 100, 100)
            ->nonQueued();
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    protected static function newFactory()
    {
        return CategoryFactory::new();
    }

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'status',
        'description',
    ];

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'categorizable');
    }
}
