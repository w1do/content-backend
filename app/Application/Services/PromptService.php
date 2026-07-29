<?php

namespace App\Application\Services;

use App\Domain\Enums\PromptCategory;
use App\Domain\Repositories\PromptRepositoryInterface;
use App\Infrastructure\Services\AI\PolzaAiService;

/**
 * Service for managing and combining SEO generation prompts.
 */
class PromptService
{
    public function __construct(
        private readonly PromptRepositoryInterface $promptRepository,
        private readonly PolzaAiService $aiService
    ) {}

    /**
     * Get rules for a specific category, combined with general rules.
     *
     * @param  PromptCategory  $category  The target category for generation.
     * @return string[] Array of rule strings from database.
     */
    public function getRulesForCategory(PromptCategory $category): array
    {
        $rules = [];

        // Add general rules first
        if ($category !== PromptCategory::General) {
            $generalPrompt = $this->promptRepository->getActiveByCategory(PromptCategory::General);
            if ($generalPrompt) {
                $rules[] = $generalPrompt->rule;
            }
        }

        // Add category specific rules
        $categoryPrompt = $this->promptRepository->getActiveByCategory($category);
        if ($categoryPrompt) {
            $rules[] = $categoryPrompt->rule;
        }

        return $rules;
    }

    /**
     * Improve the given prompt using AI.
     *
     * @param  string  $prompt  The prompt to improve.
     * @return string The improved prompt.
     */
    public function improvePrompt(string $prompt): string
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'Ты эксперт по написанию промптов для LLM. Твоя задача — проанализировать текущий промпт и улучшить его написание, сделав его более четким, структурированным и эффективным. Используй Markdown для оформления. Верни только улучшенный текст промпта без лишних комментариев.',
            ],
            [
                'role' => 'user',
                'content' => "Улучши следующий промпт:\n\n{$prompt}",
            ],
        ];

        $response = $this->aiService->chat($messages);

        return $response['choices'][0]['message']['content'] ?? $prompt;
    }
}
