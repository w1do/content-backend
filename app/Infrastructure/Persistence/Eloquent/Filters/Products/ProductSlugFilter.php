<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Filters\Products;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Фильтр для поиска товаров по слагу (slug).
 */
class ProductSlugFilter implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (is_array($value)) {
            $query->whereIn('products.slug', $value);
        } else {
            $query->where('products.slug', $value);
        }
    }
}
