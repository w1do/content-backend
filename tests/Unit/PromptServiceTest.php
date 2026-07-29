<?php

use App\Application\Services\PromptService;
use App\Domain\Entities\Prompt;
use App\Domain\Enums\PromptCategory;
use App\Domain\Repositories\PromptRepositoryInterface;
use App\Infrastructure\Services\AI\PolzaAiService;
use Mockery;

test('it combines general rules with category specific rules', function () {
    /** @var PromptRepositoryInterface|Mockery\MockInterface $repository */
    $repository = Mockery::mock(PromptRepositoryInterface::class);
    $aiService = Mockery::mock(PolzaAiService::class);

    $generalPrompt = new Prompt(1, PromptCategory::General, 'General Rule', true);
    $categoryPrompt = new Prompt(2, PromptCategory::Products, 'Product Rule', true);

    $repository->shouldReceive('getActiveByCategory')
        ->with(PromptCategory::General)
        ->andReturn($generalPrompt);

    $repository->shouldReceive('getActiveByCategory')
        ->with(PromptCategory::Products)
        ->andReturn($categoryPrompt);

    $service = new PromptService($repository, $aiService);
    $rules = $service->getRulesForCategory(PromptCategory::Products);

    expect($rules)->toBe(['General Rule', 'Product Rule']);
});

test('it returns only general rule if no category specific rule exists', function () {
    /** @var PromptRepositoryInterface|Mockery\MockInterface $repository */
    $repository = Mockery::mock(PromptRepositoryInterface::class);
    $aiService = Mockery::mock(PolzaAiService::class);

    $generalPrompt = new Prompt(1, PromptCategory::General, 'General Rule', true);

    $repository->shouldReceive('getActiveByCategory')
        ->with(PromptCategory::General)
        ->andReturn($generalPrompt);

    $repository->shouldReceive('getActiveByCategory')
        ->with(PromptCategory::Products)
        ->andReturn(null);

    $service = new PromptService($repository, $aiService);
    $rules = $service->getRulesForCategory(PromptCategory::Products);

    expect($rules)->toBe(['General Rule']);
});

test('it returns only category specific rule if no general rule exists', function () {
    /** @var PromptRepositoryInterface|Mockery\MockInterface $repository */
    $repository = Mockery::mock(PromptRepositoryInterface::class);
    $aiService = Mockery::mock(PolzaAiService::class);

    $categoryPrompt = new Prompt(2, PromptCategory::Products, 'Product Rule', true);

    $repository->shouldReceive('getActiveByCategory')
        ->with(PromptCategory::General)
        ->andReturn(null);

    $repository->shouldReceive('getActiveByCategory')
        ->with(PromptCategory::Products)
        ->andReturn($categoryPrompt);

    $service = new PromptService($repository, $aiService);
    $rules = $service->getRulesForCategory(PromptCategory::Products);

    expect($rules)->toBe(['Product Rule']);
});

test('it returns only general rule when category is general', function () {
    /** @var PromptRepositoryInterface|Mockery\MockInterface $repository */
    $repository = Mockery::mock(PromptRepositoryInterface::class);
    $aiService = Mockery::mock(PolzaAiService::class);

    $generalPrompt = new Prompt(1, PromptCategory::General, 'General Rule', true);

    $repository->shouldReceive('getActiveByCategory')
        ->with(PromptCategory::General)
        ->andReturn($generalPrompt);

    $service = new PromptService($repository, $aiService);
    $rules = $service->getRulesForCategory(PromptCategory::General);

    expect($rules)->toBe(['General Rule']);
});

test('it improves a prompt using AI service', function () {
    /** @var PromptRepositoryInterface|Mockery\MockInterface $repository */
    $repository = Mockery::mock(PromptRepositoryInterface::class);
    /** @var PolzaAiService|Mockery\MockInterface $aiService */
    $aiService = Mockery::mock(PolzaAiService::class);

    $originalPrompt = 'Make it good';
    $improvedPrompt = 'Improved: Make it good and clear';

    $aiService->shouldReceive('chat')
        ->once()
        ->with(Mockery::on(function ($messages) use ($originalPrompt) {
            return $messages[1]['content'] === "Улучши следующий промпт:\n\n{$originalPrompt}";
        }))
        ->andReturn([
            'choices' => [
                [
                    'message' => [
                        'content' => $improvedPrompt,
                    ],
                ],
            ],
        ]);

    $service = new PromptService($repository, $aiService);
    $result = $service->improvePrompt($originalPrompt);

    expect($result)->toBe($improvedPrompt);
});
