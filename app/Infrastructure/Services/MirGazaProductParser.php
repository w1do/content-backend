<?php

namespace App\Infrastructure\Services;

use App\Application\DTO\ParsedProductDTO;
use App\Domain\Services\ProductParserInterface;
use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Support\Facades\Http;

/**
 * Парсер товаров с сайта mirgaza.ru.
 */
class MirGazaProductParser implements ProductParserInterface
{
    /**
     * Парсит данные о товаре по указанному URL.
     *
     * @throws Exception
     */
    public function parse(string $url): ParsedProductDTO
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ])->get($url);

        if (! $response->successful()) {
            throw new Exception("Не удалось загрузить данные по ссылке: {$url}");
        }

        $html = $response->body();

        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        // Используем хак с XML-декларацией для корректной обработки UTF-8
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new DOMXPath($dom);

        $name = $this->extractName($xpath);
        $attributes = $this->extractAttributes($xpath);
        $description = $this->extractDescription($xpath, $dom);

        libxml_clear_errors();

        return new ParsedProductDTO(
            name: $name,
            description: $description,
            attributes: $attributes,
        );
    }

    /**
     * Извлекает название товара.
     */
    private function extractName(DOMXPath $xpath): string
    {
        $nodes = $xpath->query('//h1');
        if ($nodes->length > 0) {
            return trim($nodes->item(0)->nodeValue);
        }

        $nodes = $xpath->query('//meta[@property="og:title"]/@content');
        if ($nodes->length > 0) {
            return trim($nodes->item(0)->nodeValue);
        }

        return '';
    }

    /**
     * Извлекает описание товара, исключая блок характеристик.
     */
    private function extractDescription(DOMXPath $xpath, DOMDocument $dom): string
    {
        $nodes = $xpath->query('//div[@class="content" and @itemprop="description"]');
        if ($nodes->length === 0) {
            // Запасной вариант, если структура немного иная
            $nodes = $xpath->query('//*[@itemprop="description"]');
        }

        if ($nodes->length === 0) {
            return '';
        }

        /** @var \DOMElement $node */
        $node = $nodes->item(0);

        // Клонируем узел, чтобы не испортить исходный DOM при удалении характеристик
        $node = $node->cloneNode(true);
        $tempXPath = new DOMXPath($dom);

        // Удаляем блок характеристик из описания
        $charWrpNodes = $xpath->query('.//div[contains(@class, "char-wrp")]', $node);
        foreach ($charWrpNodes as $charWrpNode) {
            $charWrpNode->parentNode->removeChild($charWrpNode);
        }

        // Удаляем кнопки и другие лишние элементы
        $extraNodes = $xpath->query('.//button|.//script|.//style', $node);
        foreach ($extraNodes as $extraNode) {
            $extraNode->parentNode->removeChild($extraNode);
        }

        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $dom->saveHTML($child);
        }

        return trim($html);
    }

    /**
     * Извлекает характеристики товара.
     */
    private function extractAttributes(DOMXPath $xpath): array
    {
        $attributes = [];
        $rows = $xpath->query('//table[contains(@class, "props_list")]//tr');

        foreach ($rows as $row) {
            $nameNode = $xpath->query('.//td[@class="char_name"]//span[@itemprop="name"]', $row);
            $valueNode = $xpath->query('.//td[@class="char_value"]//span[@itemprop="value"]', $row);

            if ($nameNode->length > 0 && $valueNode->length > 0) {
                $name = trim($nameNode->item(0)->nodeValue);
                $value = trim($valueNode->item(0)->nodeValue);

                if ($name && $value && $name !== 'Источник') {
                    $attributes[$name] = $value;
                }
            }
        }

        return $attributes;
    }
}
