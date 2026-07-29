<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Filters\Products;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Фильтр для поиска товаров по ID категории.
 */
class ProductCategoryIdFilter implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $query->whereHas('categories', function (Builder $query) use ($value) {
            if (is_array($value)) {
                $query->whereIn('categories.id', $value);
            } else {
                $query->where('categories.id', $value);
            }
        });
    }
}
