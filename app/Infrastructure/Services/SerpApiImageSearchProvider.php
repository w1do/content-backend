<?php

namespace App\Infrastructure\Services;

use App\Application\DTO\ImageSearchResult;
use App\Domain\Services\ImageSearchProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SerpApiImageSearchProvider implements ImageSearchProviderInterface
{
    private ?string $apiKey;

    private string $baseUrl = 'https://serpapi.com/search';

    public function __construct()
    {
        $this->apiKey = config('services.serp_api.key');
    }

    /**
     * Search for an image based on a query using SerpApi Google Images engine.
     *
     * @param  string  $query  The search query.
     * @return ImageSearchResult|null The search result or null if not found/error.
     */
    public function search(string $query): ?ImageSearchResult
    {
        if (empty($this->apiKey)) {
            Log::error('SerpApi key is missing.');

            return null;
        }

        try {
            $response = Http::get($this->baseUrl, [
                'q' => $query,
                'tbm' => 'isch',
                'api_key' => $this->apiKey,
                'engine' => 'google_images',
            ]);

            if ($response->failed()) {
                Log::error('SerpApi request failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $data = $response->json();
            $imageResults = $data['images_results'] ?? [];

            if (empty($imageResults)) {
                return null;
            }

            // Get the first result
            $firstResult = $imageResults[0];

            return new ImageSearchResult(
                url: $firstResult['original'] ?? $firstResult['thumbnail'],
                title: $firstResult['title'] ?? null,
                source: $firstResult['source'] ?? null,
            );
        } catch (\Exception $e) {
            Log::error('SerpApi exception.', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }
}
