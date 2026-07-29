<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\Jobs\GenerateSeoJob;
use App\Infrastructure\Persistence\Eloquent\Concerns\HasSeo;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;
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
 * @property string|null $description
 * @property array|null $attributes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Category> $categories
 * @property-read Seo|null $seo
 *
 * @mixin Builder
 */
class Product extends Model implements HasMedia
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use HasSeo;
    use HasSlug;
    use InteractsWithMedia;

    protected static function booted(): void
    {
        static::created(function (self $product): void {
            GenerateSeoJob::dispatch($product)->afterCommit();
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

        $this->addMediaCollection('gallery')
            ->useDisk('media');
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
        return ProductFactory::new();
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'attributes',
    ];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
        ];
    }

    public function categories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categorizable');
    }
}
