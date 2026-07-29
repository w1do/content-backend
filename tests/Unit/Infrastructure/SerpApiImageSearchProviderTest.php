<?php

use App\Infrastructure\Services\SerpApiImageSearchProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

test('it can search for images and return first result', function () {
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
    $result = $provider->search('coffee');

    expect($result)->not->toBeNull()
        ->and($result->url)->toBe('https://example.com/image.jpg')
        ->and($result->title)->toBe('Example Image')
        ->and($result->source)->toBe('Example Source');
});

test('it returns null if no results found', function () {
    Config::set('services.serp_api.key', 'test_key');

    Http::fake([
        'https://serpapi.com/search*' => Http::response([
            'images_results' => [],
        ], 200),
    ]);

    $provider = new SerpApiImageSearchProvider;
    $result = $provider->search('nothing');

    expect($result)->toBeNull();
});

test('it returns null if api key is missing', function () {
    Config::set('services.serp_api.key', null);

    $provider = new SerpApiImageSearchProvider;
    $result = $provider->search('coffee');

    expect($result)->toBeNull();
});
