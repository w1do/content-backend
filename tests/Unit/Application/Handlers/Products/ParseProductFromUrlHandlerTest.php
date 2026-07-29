<?php

namespace Tests\Unit\Application\Handlers\Products;

use App\Application\DTO\ParsedProductDTO;
use App\Application\Handlers\Products\ParseProductFromUrlHandler;
use App\Application\Queries\Products\ParseProductFromUrlQuery;
use App\Domain\Features\MixedContentGenerate;
use App\Domain\Services\ProductParserInterface;
use Mockery;

it('calls parser and content generator when handle is called', function () {
    $parser = Mockery::mock(ProductParserInterface::class);
    $contentGenerator = Mockery::mock(MixedContentGenerate::class);

    $dto = new ParsedProductDTO('Name', 'Desc', ['attr' => 'val']);
    $generatedDesc = 'Generated Description';

    $parser->shouldReceive('parse')
        ->once()
        ->with('https://example.com')
        ->andReturn($dto);

    $contentGenerator->shouldReceive('generate')
        ->once()
        ->with($dto)
        ->andReturn($generatedDesc);

    $handler = new ParseProductFromUrlHandler($parser, $contentGenerator);
    $result = $handler->handle(new ParseProductFromUrlQuery('https://example.com'));

    expect($result)->toBe($dto)
        ->and($result->description)->toBe($generatedDesc);
});
