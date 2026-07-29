<?php

use App\Application\Services\PromptService;
use App\Application\Services\SeoGenerator;
use App\Domain\Enums\PromptCategory;
use App\Infrastructure\Persistence\Eloquent\Prompt;
use App\Infrastructure\Services\AI\PolzaAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it includes database rules in the AI prompt', function () {
    // Create prompts in DB
    Prompt::create([
        'category' => PromptCategory::General,
        'rule' => 'Project personality rule',
        'status' => true,
    ]);

    Prompt::create([
        'category' => PromptCategory::Products,
        'rule' => 'Product specific rule',
        'status' => true,
    ]);

    // Mock AI service
    $aiService = Mockery::mock(PolzaAiService::class);

    // We expect the chat method to be called with a prompt containing our rules
    $aiService->shouldReceive('chat')
        ->once()
        ->with(Mockery::on(function ($messages) {
            $userContent = $messages[1]['content'];

            return str_contains($userContent, 'Project personality rule') &&
                   str_contains($userContent, 'Product specific rule') &&
                   str_contains($userContent, 'Respond ONLY with a valid JSON');
        }))
        ->andReturn([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode(['title' => 'Test Title', 'description' => 'Test Description']),
                    ],
                ],
            ],
        ]);

    $generator = new SeoGenerator($aiService, app(PromptService::class));
    $result = $generator->generateFromText('Some text', PromptCategory::Products);

    expect($result)->toBe([
        'title' => 'Test Title',
        'description' => 'Test Description',
    ]);
});

test('it uses default rules when no database rules exist', function () {
    // Mock AI service
    $aiService = Mockery::mock(PolzaAiService::class);

    $aiService->shouldReceive('chat')
        ->once()
        ->with(Mockery::on(function ($messages) {
            $userContent = $messages[1]['content'];

            return str_contains($userContent, 'Title should be optimized for SEO') &&
                   str_contains($userContent, 'Respond ONLY with a valid JSON');
        }))
        ->andReturn([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode(['title' => 'Default Title', 'description' => 'Default Description']),
                    ],
                ],
            ],
        ]);

    $generator = new SeoGenerator($aiService, app(PromptService::class));
    $result = $generator->generateFromText('Some text', PromptCategory::General);

    expect($result)->toBe([
        'title' => 'Default Title',
        'description' => 'Default Description',
    ]);
});

test('it includes entity title from context in the AI prompt', function () {
    // Mock AI service
    $aiService = Mockery::mock(PolzaAiService::class);

    $aiService->shouldReceive('chat')
        ->once()
        ->with(Mockery::on(function ($messages) {
            $userContent = $messages[1]['content'];

            return str_contains($userContent, 'Title: Test Entity Name') &&
                   str_contains($userContent, 'Text: Some description content');
        }))
        ->andReturn([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode(['title' => 'T', 'description' => 'D']),
                    ],
                ],
            ],
        ]);

    $generator = new SeoGenerator($aiService, app(PromptService::class));
    $generator->generateFromText('Some description content', PromptCategory::Products, ['title' => 'Test Entity Name']);
});
