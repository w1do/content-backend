<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;
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
 * @property string|null $full_path
 * @property string $status
 * @property string|null $description
 * @property int|null $parent_id
 * @property int|null $depth
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, self> $children
 *
 * @method static \Kalnoy\Nestedset\QueryBuilder query()
 * @method static \Kalnoy\Nestedset\QueryBuilder withDepth()
 * @method static \Kalnoy\Nestedset\QueryBuilder defaultOrder()
 * @method static \Kalnoy\Nestedset\QueryBuilder whereNotDescendantOf($model)
 * @method static void fixTree()
 * @method \Kalnoy\Nestedset\Collection toTree()
 *
 * @mixin Builder
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

    protected static function booted(): void
    {
        static::creating(function (self $category): void {
            $category->full_path = $category->generateFullPath();
        });

        static::updating(function (self $category): void {
            $category->full_path = $category->generateFullPath();
        });

        static::saved(function (self $category): void {
            if ($category->wasChanged('full_path')) {
                $category->updateDescendantsFullPath();
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('main')
            ->useDisk('media')
            ->singleFile();

        $this->addMediaCollection('cover')
            ->useDisk('media')
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 100, 100)
            ->nonQueued();

        $this->addMediaConversion('cover')
            ->fit(Fit::Crop, 800, 600)
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
        'full_path',
        'status',
        'description',
    ];

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'categorizable');
    }

    /**
     * Generate the full hierarchy path of the category (e.g. `parent/child/grandchild`)
     * by concatenating the slugs of all ancestors with the category's own slug.
     *
     * The node is refreshed beforehand because `_lft`/`_rgt` boundaries may have
     * shifted due to other structural changes (e.g. descendants being inserted)
     * that happened elsewhere since this model was last loaded.
     */
    public function generateFullPath(): string
    {
        $this->refreshNode();

        $ancestors = $this->ancestors()->defaultOrder()->pluck('slug')->toArray();

        return implode('/', array_merge($ancestors, [$this->slug]));
    }

    /**
     * Recalculate and persist the `full_path` of every descendant of this category.
     * Used to keep descendants in sync after this category's own path has changed.
     */
    protected function updateDescendantsFullPath(): void
    {
        $this->refreshNode();

        $this->descendants()->get()->each(function (self $descendant): void {
            $descendant->full_path = $descendant->generateFullPath();
            $descendant->saveQuietly();
        });
    }
}
