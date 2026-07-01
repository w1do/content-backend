<?php

namespace Tests\Unit\Application\Handlers\Products;

use App\Application\DTO\ParsedProductDTO;
use App\Application\Handlers\Products\ParseProductFromUrlHandler;
use App\Application\Queries\Products\ParseProductFromUrlQuery;
use App\Domain\Services\ProductParserInterface;
use Mockery;

it('calls parser when handle is called', function () {
    $parser = Mockery::mock(ProductParserInterface::class);
    $dto = new ParsedProductDTO('Name', 'Desc', ['attr' => 'val']);

    $parser->shouldReceive('parse')
        ->once()
        ->with('https://example.com')
        ->andReturn($dto);

    $handler = new ParseProductFromUrlHandler($parser);
    $result = $handler->handle(new ParseProductFromUrlQuery('https://example.com'));

    expect($result)->toBe($dto);
});
