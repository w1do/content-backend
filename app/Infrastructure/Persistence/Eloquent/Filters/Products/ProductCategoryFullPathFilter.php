<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Filters\Products;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Фильтр для поиска товаров по полному пути категории (точное совпадение).
 */
class ProductCategoryFullPathFilter implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $query->whereHas('categories', function (Builder $query) use ($value) {
            $query->where('categories.full_path', $value);
        });
    }
}
