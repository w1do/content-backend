<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;

/**
 * DTO для фильтрации категорий.
 */
class CategoryFilterDTO extends Data
{
    /**
     * @param  mixed  $id  ID категории (может быть массивом при фильтрации по нескольким ID)
     * @param  string|null  $name  Название категории (частичное совпадение)
     * @param  string|null  $slug  Слаг категории (точное совпадение)
     * @param  string|null  $fullPath  Полный путь категории (частичное или точное совпадение)
     */
    public function __construct(
        public mixed $id = null,
        public ?string $name = null,
        public ?string $slug = null,
        #[MapInputName('full_path')]
        #[MapOutputName('full_path')]
        public ?string $fullPath = null,
    ) {}
}
