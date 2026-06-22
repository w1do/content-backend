<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

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
