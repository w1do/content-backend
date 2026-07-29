<?php

use App\Application\Handlers\Products\ParseProductFromUrlHandler;
use App\Application\Queries\Products\ParseProductFromUrlQuery;
use App\Domain\Enums\PromptCategory;
use App\Infrastructure\Persistence\Eloquent\Prompt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('generates markdown description using AI when parsing product from url', function () {
    // 1. Setup mock for MirGazaProductParser (HTML)
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
    <h1>Test Product</h1>
    <div itemprop="description">Original description</div>
    <div class="char_block">
        <table class="props_list">
            <tr itemprop="additionalProperty">
                <td class="char_name"><span itemprop="name">Color</span></td>
                <td class="char_value"><span itemprop="value">Red</span></td>
            </tr>
        </table>
    </div>
</body>
</html>
HTML;

    Http::fake([
        'mirgaza.ru/*' => Http::response($html, 200),
        'polza.ai/api/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => '# AI Generated Markdown Description',
                    ],
                ],
            ],
        ], 200),
    ]);

    config(['services.polza_ai.key' => 'test-key']);

    // 2. Setup General Prompt
    Prompt::create([
        'category' => PromptCategory::General,
        'rule' => 'Project essence: best gas equipment store.',
        'status' => true,
    ]);

    // 3. Run Handler
    $handler = app(ParseProductFromUrlHandler::class);
    $query = new ParseProductFromUrlQuery('https://mirgaza.ru/catalog/test');
    $result = $handler->handle($query);

    // 4. Assertions
    expect($result->name)->toBe('Test Product');
    expect($result->description)->toBe('# AI Generated Markdown Description');

    // Verify AI was called with correct data
    Http::assertSent(function ($request) {
        if (! str_contains($request->url(), 'polza.ai')) {
            return true;
        }

        $content = $request['messages'][1]['content'];

        return str_contains($content, 'Test Product') &&
               str_contains($content, 'Original description') &&
               str_contains($content, 'Color: Red') &&
               str_contains($content, 'Project essence: best gas equipment store.');
    });
});

it('uses original description if AI fails', function () {
    Http::fake([
        'mirgaza.ru/*' => Http::response('<html><body><h1>Test</h1><div itemprop="description">Original</div></body></html>', 200),
        'polza.ai/api/v1/chat/completions' => Http::response('Error', 500),
    ]);

    config(['services.polza_ai.key' => 'test-key']);

    $handler = app(ParseProductFromUrlHandler::class);
    $query = new ParseProductFromUrlQuery('https://mirgaza.ru/catalog/test');
    $result = $handler->handle($query);

    expect($result->description)->toBe('Original');
});

it('generates description from attributes even if original description is empty', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
    <h1>Test Product No Desc</h1>
    <div itemprop="description"></div>
    <div class="char_block">
        <table class="props_list">
            <tr itemprop="additionalProperty">
                <td class="char_name"><span itemprop="name">Material</span></td>
                <td class="char_value"><span itemprop="value">Steel</span></td>
            </tr>
        </table>
    </div>
</body>
</html>
HTML;

    Http::fake([
        'mirgaza.ru/*' => Http::response($html, 200),
        'polza.ai/api/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => '# Description from attributes',
                    ],
                ],
            ],
        ], 200),
    ]);

    config(['services.polza_ai.key' => 'test-key']);

    Prompt::create([
        'category' => PromptCategory::General,
        'rule' => 'Project essence: technical store.',
        'status' => true,
    ]);

    $handler = app(ParseProductFromUrlHandler::class);
    $query = new ParseProductFromUrlQuery('https://mirgaza.ru/catalog/test-no-desc');
    $result = $handler->handle($query);

    expect($result->description)->toBe('# Description from attributes');

    Http::assertSent(function ($request) {
        if (! str_contains($request->url(), 'polza.ai')) {
            return true;
        }

        $content = $request['messages'][1]['content'];

        return str_contains($content, 'Test Product No Desc') &&
               str_contains($content, 'Описание отсутствует') &&
               str_contains($content, 'Material: Steel');
    });
});
