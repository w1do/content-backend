<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;

/**
 * DTO для фильтрации товаров.
 */
class ProductFilterDTO extends Data
{
    /**
     * @param  mixed  $categoryId  ID категории (может быть массивом при фильтрации по нескольким ID)
     * @param  string|null  $categoryName  Название категории (частичное совпадение)
     * @param  string|null  $categoryFullPath  Полный путь категории (точное совпадение)
     */
    public function __construct(
        #[MapInputName('category_id')]
        #[MapOutputName('category_id')]
        public mixed $categoryId = null,

        #[MapInputName('category_name')]
        #[MapOutputName('category_name')]
        public ?string $categoryName = null,

        #[MapInputName('category_full_path')]
        #[MapOutputName('category_full_path')]
        public ?string $categoryFullPath = null,
    ) {}
}
