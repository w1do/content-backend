<?php

namespace App\Infrastructure\Services\AI;

use App\Infrastructure\Exceptions\PolzaAiException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PolzaAiService
{
    private string $apiKey;

    private string $baseUrl = 'https://polza.ai/api/v1';

    public function __construct()
    {
        $this->apiKey = config('services.polza_ai.key') ?? '';
    }

    /**
     * @throws PolzaAiException
     */
    public function chat(array $messages, string $model = 'gpt-4o-mini', array $options = []): array
    {
        if (empty($this->apiKey)) {
            throw new PolzaAiException('Polza AI API key is not configured.');
        }

        $payload = array_merge([
            'model' => $model,
            'messages' => $messages,
        ], $options);

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(60)
                ->post("{$this->baseUrl}/chat/completions", $payload);

            if ($response->failed()) {
                Log::error('Polza AI Request Failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new PolzaAiException("Polza AI request failed with status {$response->status()}: ".$response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            if ($e instanceof PolzaAiException) {
                throw $e;
            }
            Log::error('Polza AI Request Exception', [
                'message' => $e->getMessage(),
            ]);
            throw new PolzaAiException("Polza AI request exception: {$e->getMessage()}", 0, $e);
        }
    }
}
