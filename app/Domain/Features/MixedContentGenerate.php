<?php

namespace App\Domain\Features;

use App\Application\DTO\ParsedProductDTO;
use App\Domain\Enums\PromptCategory;
use App\Domain\Repositories\PromptRepositoryInterface;
use App\Infrastructure\Services\AI\PolzaAiService;

/**
 * Класс MixedContentGenerate отвечает за генерацию уникального контента для товаров.
 * Использует данные от парсера и ИИ для создания описания в формате Markdown.
 */
class MixedContentGenerate
{
    /**
     * @param  PromptRepositoryInterface  $promptRepository  Репозиторий для получения промптов.
     * @param  PolzaAiService  $aiService  Сервис для взаимодействия с ИИ.
     */
    public function __construct(
        private readonly PromptRepositoryInterface $promptRepository,
        private readonly PolzaAiService $aiService
    ) {}

    /**
     * Генерирует уникальное описание товара на основе данных парсинга.
     *
     * @param  ParsedProductDTO  $dto  Данные, полученные в результате парсинга товара.
     * @return string Сгенерированное описание в формате Markdown.
     *
     * @example
     * $generator = new MixedContentGenerate($repository, $aiService);
     * $markdownDescription = $generator->generate($parsedProductDTO);
     */
    public function generate(ParsedProductDTO $dto): string
    {
        $generalPrompt = $this->promptRepository->getActiveByCategory(PromptCategory::General);
        $rules = $generalPrompt ? $generalPrompt->rule : 'Создай качественное описание товара в формате Markdown.';

        $attributesString = '';
        if (! empty($dto->attributes)) {
            foreach ($dto->attributes as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }
                $attributesString .= "- {$key}: {$value}\n";
            }
        }

        $productDescription = $dto->description ?: 'Описание отсутствует';

        $prompt = <<<PROMPT
На основе предоставленных данных создай уникальное, структурированное описание товара в формате Markdown.
Используй суть проекта и правила, описанные ниже.

Данные товара:
Название: {$dto->name}
Исходное описание: {$productDescription}
Характеристики:
{$attributesString}

Правила и суть проекта (General Prompt):
{$rules}
PROMPT;

        $messages = [
            [
                'role' => 'system',
                'content' => 'Ты эксперт по копирайтингу, специализирующийся на описании технических товаров для интернет-магазина. Твоя задача — создать уникальное описание в формате Markdown, учитывая суть проекта.',
            ],
            [
                'role' => 'user',
                'content' => $prompt,
            ],
        ];

        try {
            $response = $this->aiService->chat($messages);
            $content = $response['choices'][0]['message']['content'] ?? null;

            if (! $content) {
                return $dto->description ?? '';
            }

            return $content;
        } catch (\Exception $e) {
            // В случае ошибки ИИ возвращаем исходное описание, если оно есть
            return $dto->description ?? '';
        }
    }
}
