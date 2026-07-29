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
     * Search for images based on a query using SerpApi Google Images engine.
     *
     * @param  string  $query  The search query.
     * @return ImageSearchResult[] The search results.
     */
    public function search(string $query): array
    {
        if (empty($this->apiKey)) {
            Log::error('SerpApi key is missing.');

            return [];
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

                return [];
            }

            $data = $response->json();
            $imageResults = $data['images_results'] ?? [];

            if (empty($imageResults)) {
                return [];
            }

            return collect($imageResults)
                ->take(12)
                ->map(fn (array $item) => new ImageSearchResult(
                    url: $item['original'] ?? $item['thumbnail'],
                    title: $item['title'] ?? null,
                    source: $item['source'] ?? null,
                ))
                ->all();
        } catch (\Exception $e) {
            Log::error('SerpApi exception.', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }
}
