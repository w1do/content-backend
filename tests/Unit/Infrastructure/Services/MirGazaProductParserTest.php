<?php

namespace Tests\Unit\Infrastructure\Services;

use App\Application\DTO\ParsedProductDTO;
use App\Infrastructure\Services\MirGazaProductParser;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MirGazaProductParserTest extends TestCase
{
    private MirGazaProductParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new MirGazaProductParser();
    }

    public function test_it_parses_product_data_successfully(): void
    {
        $html = '
            <html>
                <body>
                    <h1>Газовый котел</h1>
                    <div itemprop="description" class="content">
                        <p>Это описание товара.</p>
                        <div class="char-wrp">Характеристики внутри описания</div>
                    </div>
                    <table class="props_list">
                        <tr>
                            <td class="char_name"><span itemprop="name">Вес</span></td>
                            <td class="char_value"><span itemprop="value">10 кг</span></td>
                        </tr>
                    </table>
                </body>
            </html>
        ';

        Http::fake([
            'mirgaza.ru/*' => Http::response($html, 200),
        ]);

        $result = $this->parser->parse('https://mirgaza.ru/product-1');

        $this->assertInstanceOf(ParsedProductDTO::class, $result);
        $this->assertEquals('Газовый котел', $result->name);
        $this->assertEquals('<p>Это описание товара.</p>', $result->description);
        $this->assertEquals(['Вес' => '10 кг'], $result->attributes);
    }

    public function test_it_returns_null_when_description_is_empty(): void
    {
        $html = '
            <html>
                <body>
                    <h1>Товар без описания</h1>
                    <div itemprop="description" class="content">
                    </div>
                </body>
            </html>
        ';

        Http::fake([
            'mirgaza.ru/*' => Http::response($html, 200),
        ]);

        $result = $this->parser->parse('https://mirgaza.ru/product-empty');

        $this->assertEquals('Товар без описания', $result->name);
        $this->assertNull($result->description);
    }

    public function test_it_returns_null_when_description_contains_only_unwanted_elements(): void
    {
        $html = '
            <html>
                <body>
                    <h1>Товар с мусором</h1>
                    <div itemprop="description" class="content">
                        <script>alert(1)</script>
                        <style>.red { color: red; }</style>
                        <button>Купить</button>
                    </div>
                </body>
            </html>
        ';

        Http::fake([
            'mirgaza.ru/*' => Http::response($html, 200),
        ]);

        $result = $this->parser->parse('https://mirgaza.ru/product-garbage');

        $this->assertNull($result->description);
    }
}
