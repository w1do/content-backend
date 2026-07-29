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
    private function extractDescription(DOMXPath $xpath, DOMDocument $dom): ?string
    {
        $descriptionParts = [];

        // 1. Основное описание
        $nodes = $xpath->query('//div[@class="content" and @itemprop="description"]');
        if ($nodes->length === 0) {
            $nodes = $xpath->query('//*[@itemprop="description" and not(self::meta)]');
        }

        if ($nodes->length > 0) {
            $node = $nodes->item(0)->cloneNode(true);
            $this->cleanDescriptionNode($node, $xpath);
            $html = $this->getNodeInnerHtml($dom, $node);
            if (trim($html)) {
                $descriptionParts[] = $html;
            }
        }

        // 2. Комплектация
        $nodes = $xpath->query('//div[@id="complete_set_tab"]');
        if ($nodes->length > 0) {
            $node = $nodes->item(0)->cloneNode(true);
            $this->cleanDescriptionNode($node, $xpath);
            $html = $this->getNodeInnerHtml($dom, $node);
            if (trim($html)) {
                $descriptionParts[] = '<h3>Комплектация</h3>'.$html;
            }
        }

        // 3. Дополнительная вкладка (может содержать описание или совместимость)
        $nodes = $xpath->query('//div[@id="custom_tab"]');
        if ($nodes->length > 0) {
            $node = $nodes->item(0)->cloneNode(true);
            $this->cleanDescriptionNode($node, $xpath);
            $html = $this->getNodeInnerHtml($dom, $node);
            if (trim($html)) {
                $descriptionParts[] = $html;
            }
        }

        if (empty($descriptionParts)) {
            return null;
        }

        return trim(implode("\n", $descriptionParts));
    }

    /**
     * Очищает узел от лишних элементов.
     */
    private function cleanDescriptionNode(\DOMNode $node, DOMXPath $xpath): void
    {
        // Удаляем блок характеристик
        $charWrpNodes = $xpath->query('.//div[contains(@class, "char-wrp")]', $node);
        foreach ($charWrpNodes as $charWrpNode) {
            $charWrpNode->parentNode->removeChild($charWrpNode);
        }

        // Удаляем кнопки и другие лишние элементы
        $extraNodes = $xpath->query('.//button|.//script|.//style', $node);
        foreach ($extraNodes as $extraNode) {
            $extraNode->parentNode->removeChild($extraNode);
        }
    }

    /**
     * Получает внутренний HTML узла.
     */
    private function getNodeInnerHtml(DOMDocument $dom, \DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $dom->saveHTML($child);
        }

        return $html;
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
