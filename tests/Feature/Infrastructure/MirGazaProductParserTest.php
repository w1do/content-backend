<?php

use App\Infrastructure\Services\MirGazaProductParser;
use Illuminate\Support\Facades\Http;

it('can parse product data from mirgaza.ru html', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <h1>Test Product Name</h1>
    <div class="content" itemprop="description">
        <p>This is a test description.</p>
        <div class="char-wrp">
            <h2>Характеристики товара</h2>
            <div class="char_block bordered rounded3 js-scrolled">
                <table class="props_list nbg">
                    <tbody>
                        <tr itemprop="additionalProperty" itemscope="" itemtype="http://schema.org/PropertyValue">
                            <td class="char_name"><div class="props_item "><span itemprop="name">Производитель</span></div></td>
                            <td class="char_value"><span itemprop="value"> Atiker </span></td>
                        </tr>
                        <tr itemprop="additionalProperty" itemscope="" itemtype="http://schema.org/PropertyValue">
                            <td class="char_name"><div class="props_item "><span itemprop="name">Страна производителя</span></div></td>
                            <td class="char_value"><span itemprop="value"> Турция </span></td>
                        </tr>
                        <tr itemprop="additionalProperty" itemscope="" itemtype="http://schema.org/PropertyValue" data="a">
                            <td class="char_name"><div class="props_item"><span itemprop="name">Источник</span></div></td>
                            <td class="char_value"><span itemprop="value"> интернет-магазин запчастей <a href="https://mirgaza.ru/">https://mirgaza.ru/</a></span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <p>More description text.</p>
        <button class="report-error-btn">Report</button>
    </div>
</body>
</html>
HTML;

    Http::fake([
        'mirgaza.ru/*' => Http::response($html, 200),
    ]);

    $parser = new MirGazaProductParser;
    $result = $parser->parse('https://mirgaza.ru/catalog/filtr-atiker');

    expect($result->name)->toBe('Test Product Name');
    expect($result->description)->toContain('This is a test description.');
    expect($result->description)->toContain('More description text.');
    expect($result->description)->not->toContain('Характеристики товара');
    expect($result->description)->not->toContain('Report');
    expect($result->attributes)->toBe([
        'Производитель' => 'Atiker',
        'Страна производителя' => 'Турция',
    ]);
});

it('throws exception when http request fails', function () {
    Http::fake([
        'mirgaza.ru/*' => Http::response('Not Found', 404),
    ]);

    $parser = new MirGazaProductParser;
    $parser->parse('https://mirgaza.ru/catalog/unknown');
})->throws(Exception::class, 'Не удалось загрузить данные по ссылке');
