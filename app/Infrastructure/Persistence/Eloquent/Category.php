<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Kalnoy\Nestedset\NodeTrait;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    use HasSlug;
    use NodeTrait {
        NodeTrait::replicate as replicateNode;
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
