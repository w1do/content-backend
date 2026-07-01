<?php

namespace App\Application\Handlers\Products;

use App\Application\DTO\ParsedProductDTO;
use App\Application\Queries\Products\ParseProductFromUrlQuery;
use App\Domain\Services\ProductParserInterface;

/**
 * Обработчик запроса на парсинг товара по ссылке.
 */
class ParseProductFromUrlHandler
{
    /**
     * @param  ProductParserInterface  $parser  Сервис парсинга
     */
    public function __construct(
        private ProductParserInterface $parser
    ) {}

    /**
     * Выполняет парсинг товара.
     */
    public function handle(ParseProductFromUrlQuery $query): ParsedProductDTO
    {
        return $this->parser->parse($query->url);
    }
}
