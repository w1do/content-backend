<?php

namespace App\Application\Handlers\Products;

use App\Application\DTO\ParsedProductDTO;
use App\Application\Queries\Products\ParseProductFromUrlQuery;
use App\Domain\Features\MixedContentGenerate;
use App\Domain\Services\ProductParserInterface;

/**
 * Обработчик запроса на парсинг товара по ссылке.
 */
class ParseProductFromUrlHandler
{
    /**
     * @param  ProductParserInterface  $parser  Сервис парсинга
     * @param  MixedContentGenerate  $contentGenerator  Генератор контента
     */
    public function __construct(
        private ProductParserInterface $parser,
        private MixedContentGenerate $contentGenerator
    ) {}

    /**
     * Выполняет парсинг товара и генерирует уникальное описание.
     */
    public function handle(ParseProductFromUrlQuery $query): ParsedProductDTO
    {
        $dto = $this->parser->parse($query->url);

        $dto->description = $this->contentGenerator->generate($dto);

        return $dto;
    }
}
