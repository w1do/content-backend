<?php

use App\Infrastructure\Services\SerpApiImageSearchProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

test('it can search for images and return results', function () {
    Config::set('services.serp_api.key', 'test_key');

    Http::fake([
        'https://serpapi.com/search*' => Http::response([
            'images_results' => [
                [
                    'original' => 'https://example.com/image.jpg',
                    'title' => 'Example Image',
                    'source' => 'Example Source',
                ],
            ],
        ], 200),
    ]);

    $provider = new SerpApiImageSearchProvider;
    $results = $provider->search('coffee');

    expect($results)->toBeArray()
        ->and($results)->toHaveCount(1)
        ->and($results[0]->url)->toBe('https://example.com/image.jpg')
        ->and($results[0]->title)->toBe('Example Image')
        ->and($results[0]->source)->toBe('Example Source');
});

test('it returns empty array if no results found', function () {
    Config::set('services.serp_api.key', 'test_key');

    Http::fake([
        'https://serpapi.com/search*' => Http::response([
            'images_results' => [],
        ], 200),
    ]);

    $provider = new SerpApiImageSearchProvider;
    $result = $provider->search('nothing');

    expect($result)->toBeArray()
        ->and($result)->toBeEmpty();
});

test('it returns empty array if api key is missing', function () {
    Config::set('services.serp_api.key', null);

    $provider = new SerpApiImageSearchProvider;
    $result = $provider->search('coffee');

    expect($result)->toBeArray()
        ->and($result)->toBeEmpty();
});
