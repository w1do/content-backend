<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Enums\ContentType;
use App\Infrastructure\Persistence\Eloquent\Concerns\HasSeo;
use Database\Factories\ContentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property int $id
 * @property ContentType $type
 * @property string $name
 * @property string $slug
 * @property int|null $category_id
 * @property string|null $short_text Краткое описание (Markdown)
 * @property string|null $full_text Полный текст (Markdown)
 * @property int $views
 * @property array|null $tags
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Seo|null $seo
 *
 * @mixin Builder
 */
class Content extends Model
{
    /** @use HasFactory<ContentFactory> */
    use HasFactory;

    use HasSeo;
    use HasSlug;

    protected $fillable = [
        'type',
        'name',
        'slug',
        'category_id',
        'short_text',
        'full_text',
        'views',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'type' => ContentType::class,
            'tags' => 'array',
            'views' => 'integer',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    protected static function newFactory()
    {
        return ContentFactory::new();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
