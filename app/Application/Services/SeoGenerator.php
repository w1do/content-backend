<?php

namespace App\Application\Services;

use App\Domain\Enums\PromptCategory;
use App\Infrastructure\Services\AI\PolzaAiService;
use Illuminate\Support\Facades\Log;

class SeoGenerator
{
    public function __construct(
        private readonly PolzaAiService $aiService,
        private readonly PromptService $promptService
    ) {}

    /**
     * Generate SEO title and description from text.
     *
     * @param  string  $text  The main content/description to generate from.
     * @param  PromptCategory  $category  The category for rules.
     * @param  array{title?: string}  $context  Additional context like entity title.
     * @return array{title: string|null, description: string|null}|null
     */
    public function generateFromText(string $text, PromptCategory $category = PromptCategory::General, array $context = []): ?array
    {
        if (empty($text)) {
            return null;
        }

        $dbRules = $this->promptService->getRulesForCategory($category);

        $technicalRules = [
            'Title should be optimized for SEO, maximum 60 characters.',
            'Description should be optimized for SEO, maximum 160 characters.',
            'Respond ONLY with a valid JSON object containing "title" and "description" keys.',
            'Use the same language as the input text.',
            'Do not include any markdown formatting or extra text.',
        ];

        $rules = ! empty($dbRules) ? array_merge($dbRules, $technicalRules) : $technicalRules;

        $rulesString = implode("\n- ", $rules);

        $prompt = "Generate SEO Title and Meta Description based on the following information.\n";

        if (! empty($context['title'])) {
            $prompt .= 'Title: '.$context['title']."\n";
        }

        $prompt .= 'Text: '.$text."\n\n".
                  "Rules:\n- ".
                  $rulesString;

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
