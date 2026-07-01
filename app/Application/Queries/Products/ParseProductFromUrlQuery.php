<?php

namespace App\Application\Queries\Products;

/**
 * Запрос на парсинг товара по ссылке.
 */
class ParseProductFromUrlQuery
{
    /**
     * @param  string  $url  URL страницы товара
     */
    public function __construct(
        public string $url
    ) {}
}
