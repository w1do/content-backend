<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Filters\Categories;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Фильтр для поиска категорий по ID.
 * Поддерживает как одиночное значение, так и массив ID.
 */
class CategoryIdFilter implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);

            return;
        }

        $query->where('id', $value);
    }
}
