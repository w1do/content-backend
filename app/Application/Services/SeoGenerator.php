<?php

namespace App\Application\Services;

use App\Infrastructure\Services\AI\PolzaAiService;
use Illuminate\Support\Facades\Log;

class SeoGenerator
{
    public function __construct(
        private readonly PolzaAiService $aiService
    ) {}

    /**
     * Generate SEO title and description from text.
     *
     * @return array{title: string|null, description: string|null}|null
     */
    public function generateFromText(string $text): ?array
    {
        if (empty($text)) {
            return null;
        }

        $prompt = "Generate SEO Title and Meta Description based on the following text.\n".
                  'Text: '.$text."\n\n".
                  "Rules:\n".
                  "- Title should be optimized for SEO, maximum 60 characters.\n".
                  "- Description should be optimized for SEO, maximum 160 characters.\n".
                  "- Respond ONLY with a valid JSON object containing \"title\" and \"description\" keys.\n".
                  "- Use the same language as the input text.\n".
                  '- Do not include any markdown formatting or extra text.';

        try {
            $response = $this->aiService->chat([
                ['role' => 'system', 'content' => 'You are an SEO specialist. Your goal is to create compelling, high-converting meta titles and descriptions.'],
                ['role' => 'user', 'content' => $prompt],
            ]);

            $content = $response['choices'][0]['message']['content'] ?? '';

            // Basic cleanup for common AI response styles
            if (str_contains($content, '```')) {
                $content = preg_replace('/^```(?:json)?\s+|\s+```$/', '', trim($content));
            }
            $content = trim($content);

            $result = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to parse SEO generator response', [
                    'content' => $content,
                    'error' => json_last_error_msg(),
                ]);

                return null;
            }

            return [
                'title' => $result['title'] ?? null,
                'description' => $result['description'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('SEO Generator Error', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
