<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Filters\Categories;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Фильтр для точного поиска по полному пути категории.
 */
class CategoryFullPathFilter implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $query->where('full_path', $value);
    }
}
